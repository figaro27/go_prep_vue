<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Store\StoreController;
use App\Http\Requests\StoreMealRequest;
use App\Http\Requests\UpdateMealRequest;
use App\Meal;
use App\MealSubscription;
use App\MealSubscriptionComponent;
use App\MealSubscriptionAddon;
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
use Exception;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;

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
                'frequencyType',
                'child_store_ids',
                'hideFromMenu'
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

    public function checkForReplacements(Request $request)
    {
        $meal = Meal::where('id', $request->get('id'))
            ->with(['components', 'addons'])
            ->first();
        $action = $request->get('action');

        if ($action == 'activate') {
            $meal->active = 1;
            $meal->update();
            return;
        }

        $subscriptions = $meal->subscriptions
            ->where('status', '!=', 'cancelled')
            ->count();

        $mealMealPackages = MealMealPackage::whereHas('meal_package', function (
            $mealPkg
        ) {
            $mealPkg->where('deleted_at', '=', null);
        })
            ->where('meal_id', $meal->id)
            ->count();

        $mealMealPackageSizes = MealMealPackageSize::whereHas(
            'meal_package_size',
            function ($mealPkg) {
                $mealPkg->where('deleted_at', '=', null);
            }
        )
            ->where('meal_id', $meal->id)
            ->count();

        $mealMealPackageComponentOptions = MealMealPackageComponentOption::where(
            'meal_id',
            $meal->id
        )->count();
        $mealMealPackageAddons = MealMealPackageAddon::where(
            'meal_id',
            $meal->id
        )->count();

        if (
            $subscriptions > 0 ||
            $mealMealPackages > 0 ||
            $mealMealPackageSizes > 0 ||
            $mealMealPackageComponentOptions > 0 ||
            $mealMealPackageAddons > 0
        ) {
            return $meal;
        } else {
            if ($action == 'deactivate') {
                $meal->active = 0;
                $meal->update();
            } else {
                $meal->delete();
            }
        }
    }

    public function checkForVariationReplacements(Request $request)
    {
        $meal = Meal::where('id', $request->get('id'))
            ->with(['sizes', 'components', 'addons'])
            ->first();
        $sizes = $meal->sizes;
        $components = $meal->components;
        $addons = $meal->addons;

        $replacementSizeIds = [];
        $replacementComponentIds = [];
        $replacementAddonIds = [];

        // Check if the meal's sizes exist in any active or paused subscription and meal packages
        foreach ($sizes as $size) {
            $mealSubs = MealSubscription::where('meal_size_id', $size->id)
                ->whereHas('subscription', function ($sub) {
                    $sub->where('status', '!=', 'cancelled');
                })
                ->count();

            $mealMealPackages = MealMealPackage::where(
                'meal_size_id',
                $size->id
            )->count();

            $mealMealPackageSizes = MealMealPackageSize::where(
                'meal_size_id',
                $size->id
            )->count();

            $mealMealPackageComponentOptions = MealMealPackageComponentOption::where(
                'meal_size_id',
                $size->id
            )->count();

            $mealMealPackageAddons = MealMealPackageAddon::where(
                'meal_size_id',
                $size->id
            )->count();

            if (
                $mealSubs > 0 ||
                $mealMealPackages > 0 ||
                $mealMealPackageSizes > 0 ||
                $mealMealPackageComponentOptions ||
                $mealMealPackageAddons
            ) {
                $replacementSizeIds[] = $size->id;
            }
        }

        foreach ($sizes as $size) {
            $mealSubs = MealSubscription::where('meal_size_id', $size->id)
                ->whereHas('subscription', function ($sub) {
                    $sub->where('status', '!=', 'cancelled');
                })
                ->count();

            if (
                ($mealSubs > 0 ||
                    $mealMealPackages > 0 ||
                    $mealMealPackageSizes > 0 ||
                    $mealMealPackageComponentOptions ||
                    $mealMealPackageAddons) &&
                !in_array($size->id, $replacementSizeIds)
            ) {
                $replacementSizeIds[] = $size->id;
            }
        }

        // Check if the meal's components exist in any active or paused subscription only (meal components are not found on packages)

        foreach ($components as $component) {
            $mealSubs = MealSubscriptionComponent::where(
                'meal_component_id',
                $component->id
            )
                ->whereHas('mealSubscription', function ($mealSub) {
                    $mealSub->whereHas('subscription', function ($sub) {
                        $sub->where('status', '!=', 'cancelled');
                    });
                })
                ->count();

            if (
                $mealSubs > 0 &&
                !in_array($component->id, $replacementComponentIds)
            ) {
                $replacementComponentIds[] = $component->id;
            }
        }

        foreach ($addons as $addon) {
            $mealSubs = MealSubscriptionAddon::where(
                'meal_addon_id',
                $addon->id
            )
                ->whereHas('mealSubscription', function ($mealSub) {
                    $mealSub->whereHas('subscription', function ($sub) {
                        $sub->where('status', '!=', 'cancelled');
                    });
                })
                ->count();

            if ($mealSubs > 0 && !in_array($addon->id, $replacementAddonIds)) {
                $replacementAddonIds[] = $addon->id;
            }
        }

        $replace = false;
        if (
            count($replacementSizeIds) > 0 ||
            count($replacementComponentIds) > 0 ||
            count($replacementAddonIds) > 0
        ) {
            $replace = true;
        }

        return [
            'replace' => $replace,
            'sizes' => $replacementSizeIds,
            'components' => $replacementComponentIds,
            'addons' => $replacementAddonIds
        ];
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
        try {
            $result = $mealReplacementService->replaceMeal($params);

            Log::info(
                sprintf(
                    'Replaced meal %d with %d successfully',
                    $mealId,
                    $subId
                ),
                [
                    'result' => $result
                ]
            );
        } catch (Exception $e) {
            Log::info(
                sprintf('Failed to subtitute meal %d with %d', $mealId, $subId),
                [
                    'error' => $e->getMessage(),
                    'exception' => $e
                ]
            );

            return response()->json(
                [
                    'error' => 'Failed to substitute meal',
                    'detail' => $e->getMessage()
                ],
                500
            );
        }

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
            $transferVariations
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

        /*$mealMealPackages = MealMealPackage::where('meal_id', $mealId)->get();
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
        }*/

        $params = new MealReplacementParams(
            $mealId,
            $subId,
            collect(),
            collect(),
            collect()
        );

        $mealReplacementService = new MealReplacementService();
        try {
            $result = $mealReplacementService->replaceMeal($params);

            Log::info(
                sprintf(
                    'Replaced meal %d with %d successfully',
                    $mealId,
                    $subId
                ),
                [
                    'result' => $result
                ]
            );
        } catch (Exception $e) {
            Log::info(
                sprintf('Failed to subtitute meal %d with %d', $mealId, $subId),
                [
                    'error' => $e->getMessage(),
                    'exception' => $e
                ]
            );

            return response()->json(
                [
                    'error' => 'Failed to substitute meal',
                    'detail' => $e->getMessage()
                ],
                500
            );
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
        $meal->servingUnitQuantity = $request->get('servingUnitQuantity');
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
