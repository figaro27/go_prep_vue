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
use App\Services\MealReplacement\MealReplacementParams;
use App\Services\MealReplacement\MealReplacementService;
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
                'expirationDays',
                'frequencyType'
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

        $params = new MealReplacementParams(
            $mealId,
            $subId,
            $substituteMealSizes,
            $substituteMealAddons,
            $substituteMealComponentOptions
        );

        $mealReplacementService = new MealReplacementService();
        $result = $mealReplacementService->replaceMeal($params);

        /*
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
            if ($mealMealPackage->meal_size_id === null) {
                $existingMealMealPackage = MealMealPackage::where([
                    'meal_id' => $subId,
                    'meal_size_id' => null,
                    'meal_package_id' => $mealMealPackage->meal_package_id
                ])->first();
                if ($existingMealMealPackage) {
                    $quantity =
                        $existingMealMealPackage->quantity +
                        $mealMealPackage->quantity;
                    $existingMealMealPackage->quantity = $quantity;
                    $existingMealMealPackage->update();
                    $mealMealPackage->delete();
                } else {
                    $mealMealPackage->meal_id = $subId;
                    $mealMealPackage->update();
                }
            }
        }

        foreach ($mealMealPackageSizes as $mealMealPackageSize) {
            if ($mealMealPackageSize->meal_size_id === null) {
                $existingMealMealPackageSize = MealMealPackageSize::where([
                    'meal_id' => $subId,
                    'meal_size_id' => null,
                    'meal_package_size_id' =>
                        $mealMealPackageSize->meal_package_size_id
                ])->first();
                if ($existingMealMealPackageSize) {
                    $quantity =
                        $existingMealMealPackageSize->quantity +
                        $mealMealPackageSize->quantity;
                    $existingMealMealPackageSize->quantity = $quantity;
                    $existingMealMealPackageSize->update();
                    $mealMealPackageSize->delete();
                } else {
                    $mealMealPackageSize->meal_id = $subId;
                    $mealMealPackageSize->update();
                }
            }
        }

        foreach (
            $mealMealPackageComponentOptions
            as $mealMealPackageComponentOption
        ) {
            if ($mealMealPackageComponentOption->meal_size_id === null) {
                $existingMealMealPackageComponentOption = MealMealPackageComponentOption::where(
                    [
                        'meal_id' => $subId,
                        'meal_size_id' => null,
                        'meal_package_component_option_id' =>
                            $mealMealPackageComponentOption->meal_package_component_option_id
                    ]
                )->first();
                if ($existingMealMealPackageComponentOption) {
                    $quantity =
                        $existingMealMealPackageComponentOption->quantity +
                        $mealMealPackageComponentOption->quantity;
                    $existingMealMealPackageComponentOption->quantity = $quantity;
                    $existingMealMealPackageComponentOption->update();
                    $mealMealPackageComponentOption->delete();
                } else {
                    $mealMealPackageComponentOption->meal_id = $subId;
                    $mealMealPackageComponentOption->update();
                }
            }
        }
        foreach ($mealMealPackageAddons as $mealMealPackageAddon) {
            if ($mealMealPackageAddon->meal_size_id === null) {
                $existingMealMealPackageAddon = MealMealPackageAddon::where([
                    'meal_id' => $subId,
                    'meal_size_id' => null,
                    'meal_package_addon_id' =>
                        $mealMealPackageAddon->meal_package_addon_id
                ])->first();
                if ($existingMealMealPackageAddon) {
                    $quantity =
                        $existingMealMealPackageAddon->quantity +
                        $mealMealPackageAddon->quantity;
                    $existingMealMealPackageAddon->quantity = $quantity;
                    $existingMealMealPackageAddon->update();
                    $mealMealPackageAddon->delete();
                } else {
                    $mealMealPackageAddon->meal_id = $subId;
                    $mealMealPackageAddon->update();
                }
            }
        }

        foreach ($substituteMealSizes as $oldMealSizeId => $newMealSizeId) {
            foreach ($mealMealPackages as $mealMealPackage) {
                if ($mealMealPackage->meal_size_id === $oldMealSizeId) {
                    $mealMealPackage->meal_size_id = $newMealSizeId;
                    $mealMealPackage->update();
                }
            }
            foreach ($mealMealPackageSizes as $mealMealPackageSize) {
                if ($mealMealPackageSize->meal_size_id === $oldMealSizeId) {
                    $existingMealMealPackageSize = MealMealPackageSize::where([
                        'meal_id' => $subId,
                        'meal_size_id' => $newMealSizeId,
                        'meal_package_size_id' =>
                            $mealMealPackageSize->meal_package_size_id
                    ])->first();

                    if ($existingMealMealPackageSize) {
                        $quantity =
                            $existingMealMealPackageSize->quantity +
                            $mealMealPackageSize->quantity;
                        $existingMealMealPackageSize->quantity = $quantity;
                        $existingMealMealPackageSize->update();
                        $mealMealPackageSize->delete();
                    } else {
                        $mealMealPackageSize->meal_id = $subId;
                        $mealMealPackageSize->meal_size_id = $newMealSizeId;
                        $mealMealPackageSize->update();
                    }
                }
            }
            foreach (
                $mealMealPackageComponentOptions
                as $mealMealPackageComponentOption
            ) {
                if (
                    $mealMealPackageComponentOption->meal_size_id ===
                    $oldMealSizeId
                ) {
                    $existingMealMealPackageComponentOption = MealMealPackageComponentOption::where(
                        [
                            'meal_id' => $subId,
                            'meal_size_id' => $newMealSizeId,
                            'meal_package_component_option_id' =>
                                $mealMealPackageComponentOption->meal_package_component_option_id
                        ]
                    )->first();

                    if ($existingMealMealPackageComponentOption) {
                        $quantity =
                            $existingMealMealPackageComponentOption->quantity +
                            $mealMealPackageComponentOption->quantity;
                        $existingMealMealPackageComponentOption->quantity = $quantity;
                        $existingMealMealPackageComponentOption->update();
                        $mealMealPackageComponentOption->delete();
                    } else {
                        $mealMealPackageComponentOption->meal_id = $subId;
                        $mealMealPackageComponentOption->meal_size_id = $newMealSizeId;
                        $mealMealPackageComponentOption->update();
                    }
                }
            }
            foreach ($mealMealPackageAddons as $mealMealPackageAddon) {
                if ($mealMealPackageAddon->meal_size_id === $oldMealSizeId) {
                    $existingMealMealPackageAddon = MealMealPackageAddon::where(
                        [
                            'meal_id' => $subId,
                            'meal_size_id' => $newMealSizeId,
                            'meal_package_addon_id' =>
                                $mealMealPackageAddon->meal_package_addon_id
                        ]
                    )->first();

                    if ($existingMealMealPackageAddon) {
                        $quantity =
                            $existingMealMealPackageAddon->quantity +
                            $mealMealPackageAddon->quantity;
                        $existingMealMealPackageAddon->quantity = $quantity;
                        $existingMealMealPackageAddon->update();
                        $mealMealPackageAddon->delete();
                    } else {
                        $mealMealPackageAddon->meal_id = $subId;
                        $mealMealPackageAddon->meal_size_id = $newMealSizeId;
                        $mealMealPackageAddon->update();
                    }
                }
            }
        }
        */

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
