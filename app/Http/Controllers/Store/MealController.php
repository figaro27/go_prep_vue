<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Store\StoreController;
use App\Http\Requests\StoreMealRequest;
use App\Http\Requests\UpdateMealRequest;
use App\Meal;
use App\MealPackage;
use App\MealMealPackage;
use App\MealMealPackageSize;
use App\MealMealPackageComponentOption;
use App\MealMealPackageAddon;
use App\MealSize;
use App\MealPackageComponentOption;
use App\MealPackageAddon;
use Illuminate\Http\Request;

class MealController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->has('meals')
            ? $this->store
                ->meals()
                ->with([
                    'orders',
                    'tags',
                    'ingredients',
                    'sizes',
                    'attachments'
                ])
                ->without(['allergies', 'categories', 'store'])
                ->get()
            : [];
    }

    public function getStoreMeals()
    {
        $id = auth()->id;
        $storeId = Store::where('user_id', $id)
            ->pluck('id')
            ->first();

        return Meal::getStoreMeals($storeId);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMealRequest $request)
    {
        return Meal::storeMeal($request);
    }

    public function storeAdmin(Request $request)
    {
        return Meal::storeMealAdmin($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        return Meal::getMeal($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function edit(Meal $meal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMealRequest $request, $id)
    {
        return Meal::updateMeal(
            $id,
            $request->only([
                'active',
                'title',
                'description',
                'instructions',
                'price',
                'category_ids',
                'tag_ids',
                'allergy_ids',
                'delivery_day_ids',
                'featured_image',
                'gallery',
                'ingredients',
                'sizes',
                'default_size_title',
                'components',
                'addons',
                'macros',
                'production_group_id',
                'salesTax',
                'stock',
                'expirationDays'
            ]),
            'menu'
        );
    }

    public function updateActive(Request $request, $id)
    {
        if ($request->has('active')) {
            return Meal::updateActive($id, $request->get('active'));
        }
    }

    public function deactivateAndReplace(Request $request)
    {
        $mealId = $request->get('mealId');
        $subId = $request->get('substituteId');
        $replaceOnly = $request->get('replaceOnly');
        $transferVariations = (bool) $request->get('transferVariations', false);
        $substituteMealSizes = collect(
            $request->get('substituteMealSizes', [])
        );
        $substituteMealAddons = collect(
            $request->get('substituteMealAddons', [])
        );
        $substituteMealComponentOptions = collect(
            $request->get('substituteMealComponentOptions', [])
        );

        $meal = $this->store->meals()->find($mealId);

        if ($subId) {
            $sub = $this->store->meals()->find($subId);
        }

        if (!$meal) {
            return response()->json(
                [
                    'error' => 'Invalid meal ID'
                ],
                400
            );
        }

        if ($meal->substitute && !$sub) {
            return response()->json(
                [
                    'error' => 'Invalid substitute meal ID'
                ],
                400
            );
        }

        $mealMealPackages = MealMealPackage::where('meal_id', $mealId)->get();
        $mealMealPackageSizes = MealMealPackageSize::where(
            'meal_id',
            $mealId
        )->get();

        $mealMealPackageComponentOptions = MealMealPackageComponentOption::Where(
            'meal_id',
            $mealId
        )->get();
        $mealMealPackageAddons = MealMealPackageAddon::where(
            'meal_id',
            $mealId
        )->get();

        foreach ($mealMealPackages as $mealMealPackage) {
            if (count($substituteMealSizes) > 0) {
                foreach (
                    $substituteMealSizes
                    as $oldMealSizeId => $newMealSizeId
                ) {
                    $existing = MealMealPackage::where([
                        'meal_id' => $subId,
                        'meal_size_id' => $oldMealSizeId,
                        'meal_package_id' => $mealMealPackage->meal_package_id
                    ])->first();

                    if (!$existing) {
                        $mealMealPackage->update([
                            'meal_id' => $subId,
                            'meal_size_id' => $newMealSizeId
                        ]);
                    } else {
                        $existingQuantity = $existing->quantity;
                        $quantity = $mealMealPackage->quantity;
                        $existing->update([
                            'quantity' => $existingQuantity + $quantity
                        ]);
                        $mealMealPackage->delete();
                    }
                }
            } else {
                $existing = MealMealPackage::where([
                    'meal_id' => $subId,
                    'meal_package_id' => $mealMealPackage->meal_package_id
                ])->first();

                if (!$existing) {
                    $mealMealPackage->update(['meal_id' => $subId]);
                } else {
                    $existingQuantity = $existing->quantity;
                    $quantity = $mealMealPackage->quantity;
                    $existing->update([
                        'quantity' => $existingQuantity + $quantity
                    ]);
                    $mealMealPackage->delete();
                }
            }
        }

        foreach ($mealMealPackageSizes as $mealMealPackageSize) {
            if (count($substituteMealSizes) > 0) {
                foreach (
                    $substituteMealSizes
                    as $oldMealSizeId => $newMealSizeId
                ) {
                    $existing = MealMealPackageSize::where([
                        'meal_id' => $subId,
                        'meal_size_id' => $oldMealSizeId,
                        'meal_package_size_id' =>
                            $mealMealPackageSize->meal_package_size_id
                    ])->first();

                    if (!$existing) {
                        $mealMealPackageSize->update([
                            'meal_id' => $subId,
                            'meal_size_id' => $newMealSizeId
                        ]);
                    } else {
                        $existingQuantity = $existing->quantity;
                        $quantity = $mealMealPackageSize->quantity;
                        $existing->update([
                            'quantity' => $existingQuantity + $quantity
                        ]);
                        $mealMealPackageSize->delete();
                    }
                }
            } else {
                $existing = MealMealPackageSize::where([
                    'meal_id' => $subId,
                    'meal_package_size_id' =>
                        $mealMealPackageSize->meal_package_size_id
                ])->first();

                if (!$existing) {
                    $mealMealPackageSize->update(['meal_id' => $subId]);
                } else {
                    $existingQuantity = $existing->quantity;
                    $quantity = $mealMealPackageSize->quantity;
                    $existing->update([
                        'quantity' => $existingQuantity + $quantity
                    ]);
                    $mealMealPackageSize->delete();
                }
            }
        }

        foreach (
            $mealMealPackageComponentOptions
            as $mealMealPackageComponentOption
        ) {
            if (count($substituteMealSizes) > 0) {
                foreach (
                    $substituteMealSizes
                    as $oldMealSizeId => $newMealSizeId
                ) {
                    $existing = MealMealPackageComponentOption::where([
                        'meal_id' => $subId,
                        'meal_size_id' => $oldMealSizeId,
                        'meal_package_component_option_id' =>
                            $mealMealPackageComponentOption->meal_package_component_option_id
                    ])->first();

                    if (!$existing) {
                        $mealMealPackageComponentOption->update([
                            'meal_id' => $subId,
                            'meal_size_id' => $newMealSizeId
                        ]);
                    } else {
                        $mealPackageComponentOption = MealPackageComponentOption::where(
                            'id',
                            $existing->meal_package_component_option_id
                        )->first();
                        if (
                            $mealPackageComponentOption &&
                            !$mealPackageComponentOption->selectable
                        ) {
                            $existingQuantity = $existing->quantity;
                            $quantity =
                                $mealMealPackageComponentOption->quantity;
                            $existing->update([
                                'quantity' => $existingQuantity + $quantity
                            ]);
                            $mealMealPackageComponentOption->delete();
                        } else {
                            $mealMealPackageComponentOption->delete();
                        }
                    }
                }
            } else {
                $existing = MealMealPackageComponentOption::where([
                    'meal_id' => $subId,
                    'meal_package_component_option_id' =>
                        $mealMealPackageComponentOption->meal_package_component_option_id
                ])->first();

                if (!$existing) {
                    $mealMealPackageComponentOption->update([
                        'meal_id' => $subId
                    ]);
                } else {
                    $mealPackageComponentOption = MealPackageComponentOption::where(
                        'id',
                        $existing->meal_package_component_option_id
                    )->first();
                    if (
                        $mealPackageComponentOption &&
                        !$mealPackageComponentOption->selectable
                    ) {
                        $existingQuantity = $existing->quantity;
                        $quantity = $mealMealPackageComponentOption->quantity;
                        $existing->update([
                            'quantity' => $existingQuantity + $quantity
                        ]);
                        $mealMealPackageComponentOption->delete();
                    } else {
                        $mealMealPackageComponentOption->delete();
                    }
                }
            }
        }

        foreach ($mealMealPackageAddons as $mealMealPackageAddon) {
            if (count($substituteMealSizes) > 0) {
                foreach (
                    $substituteMealSizes
                    as $oldMealSizeId => $newMealSizeId
                ) {
                    $existing = MealMealPackageAddon::where([
                        'meal_id' => $subId,
                        'meal_size_id' => $oldMealSizeId,
                        'meal_package_addon_id' =>
                            $mealMealPackageAddon->meal_package_addon_id
                    ])->first();

                    if (!$existing) {
                        $mealMealPackageAddon->update([
                            'meal_id' => $subId,
                            'meal_size_id' => $newMealSizeId
                        ]);
                    } else {
                        $mealPackageAddon = MealPackageAddon::where(
                            'id',
                            $existing->meal_package_addon_id
                        )->first();
                        if (
                            $mealPackageAddon &&
                            !$mealPackageAddon->selectable
                        ) {
                            $existingQuantity = $existing->quantity;
                            $quantity = $mealMealPackageAddon->quantity;
                            $existing->update([
                                'quantity' => $existingQuantity + $quantity
                            ]);
                            $mealMealPackageAddon->delete();
                        } else {
                            $mealMealPackageAddon->delete();
                        }
                    }
                }
            } else {
                $existing = MealMealPackageAddon::where([
                    'meal_id' => $subId,
                    'meal_package_addon_id' =>
                        $mealMealPackageAddon->meal_package_addon_id
                ])->first();

                if (!$existing) {
                    $mealMealPackageAddon->update(['meal_id' => $subId]);
                } else {
                    $mealPackageAddon = MealPackageAddon::where(
                        'id',
                        $existing->meal_package_addon_id
                    )->first();
                    if ($mealPackageAddon && !$mealPackageAddon->selectable) {
                        $existingQuantity = $existing->quantity;
                        $quantity = $mealMealPackageAddon->quantity;
                        $existing->update([
                            'quantity' => $existingQuantity + $quantity
                        ]);
                        $mealMealPackageAddon->delete();
                    } else {
                        $mealMealPackageAddon->delete();
                    }
                }
            }
        }

        if ($this->store) {
            $this->store->setTimezone();
            $this->store->menu_update_time = date('Y-m-d H:i:s');
            $this->store->save();
        }

        return Meal::deleteMeal(
            $mealId,
            $subId,
            $replaceOnly,
            $transferVariations,
            $substituteMealSizes,
            $substituteMealAddons,
            $substituteMealComponentOptions
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $meal = $this->store->meals()->find($id);
        $mealId = $meal->id;
        $subId = intval($request->input('substitute_id'));

        $mealMealPackages = MealMealPackage::where('meal_id', $mealId)->get();
        $subCheck = false;

        foreach ($mealMealPackages as $mealMealPackage) {
            $mealPackageId = $mealMealPackage->meal_package_id;
            $packageMeals = MealMealPackage::where(
                'meal_package_id',
                $mealPackageId
            )->get();
            $quantity = $mealMealPackage->quantity;

            foreach ($packageMeals as $packageMeal) {
                if ($packageMeal->meal_id === $subId) {
                    $subQuantity = $packageMeal->quantity;
                    $packageMeal->update([
                        'quantity' => $quantity + $subQuantity
                    ]);
                    $mealMealPackage->delete();
                    $subCheck = true;
                }
            }
        }

        if (!$subCheck) {
            foreach ($mealMealPackages as $mealMealPackage) {
                $mealMealPackage->update(['meal_id' => $subId]);
            }
        }

        if ($this->store) {
            $this->store->setTimezone();
            $this->store->menu_update_time = date('Y-m-d H:i:s');
            $this->store->save();
        }

        return Meal::deleteMeal($id, $subId);
    }

    public function destroyMealNonSubtitute(Request $request)
    {
        if ($this->store) {
            $this->store->setTimezone();
            $this->store->menu_update_time = date('Y-m-d H:i:s');
            $this->store->save();
        }

        $meal = $this->store->meals()->find($request->id);
        $meal->delete();
    }

    public function saveMealServings(Request $request)
    {
        $mealSizeId = $request->get('meal_size_id');
        if ($mealSizeId) {
            $meal = MealSize::where('id', $mealSizeId)->first();
        } else {
            $meal = Meal::where('id', $request->get('id'))->first();
        }

        $meal->servingsPerMeal = $request->get('servingsPerMeal');
        if ($request->get('servingSizeUnit') === null) {
            $meal->servingSizeUnit = '';
        } else {
            $meal->servingSizeUnit = $request->get('servingSizeUnit');
        }
        $meal->save();
    }

    public function getLatestMealSize(Request $request)
    {
        $mealId = $request->get('id');

        return MealSize::where('meal_id', $mealId)
            ->pluck('id')
            ->last();
    }
}
