<?php

namespace App\Services\MealReplacement;

use App\Meal;
use App\MealAddon;
use App\MealComponentOption;
use App\MealMealPackage;
use App\MealMealPackageAddon;
use App\MealMealPackageComponentOption;
use App\MealMealPackageSize;
use App\MealSize;
use App\MealSubscription;
use App\MealSubscriptionAddon;
use App\MealSubscriptionComponent;
use App\Services\MealReplacement\Exception\InvalidMappingException;
use App\Services\MealReplacement\Exception\MissingSubscriptionForMealException;
use App\Services\MealReplacement\Exception\StoreMismatchException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MealReplacementService
{
    const ERROR_STORE_MISMATCH = 'Meal and substitute meal are owned by different stores';

    /**
     * Perform meal replacement
     *
     * @param MealReplacementParams $params
     * @return MealReplacementResult
     */
    public function replaceMeal(MealReplacementParams $params)
    {
        // Handle replace
        try {
            return $this->handleReplaceMeal($params);
        } catch (Exception $e) {
            Log::error('Failed to replace meal', [
                'meal_id' => $params->getMealId(),
                'substitute_meal_id' => $params->getSubstituteMealId(),
                'exception' => $e
            ]);
            throw $e;
        }
    }

    protected function handleReplaceMeal(MealReplacementParams $params)
    {
        $meal = Meal::findOrFail($params->getMealId());
        $substituteMeal = Meal::findOrFail($params->getSubstituteMealId());

        // Pre-process checks
        $this->verifyParams($params);

        try {
            // Transaction start
            DB::beginTransaction();

            $result = new MealReplacementResult();

            $this->activateSubstitute($substituteMeal);
            $this->replaceMealInPackages($params, $result);
            $this->replaceMealInSubscriptions($params, $result);

            $substituteMeal->activate();

            DB::commit();

            return $result;
        } catch (Exception $e) {
            // Catch, cancel transaction
            DB::rollBack();

            throw $e;
        }
    }

    protected function verifyParams(MealReplacementParams $params)
    {
        $meal = Meal::findOrFail($params->getMealId());
        $substituteMeal = Meal::findOrFail($params->getSubstituteMealId());

        if ($meal->store_id !== $substituteMeal->store_id) {
            throw new StoreMismatchException(self::ERROR_STORE_MISMATCH);
        }

        $this->verifySizeMapping($params, $meal, $substituteMeal);
        $this->verifyAddonMapping($params, $meal, $substituteMeal);
        $this->verifyComponentOptionMapping($params, $meal, $substituteMeal);
    }

    protected function verifySizeMapping(
        MealReplacementParams $params,
        Meal $meal,
        Meal $substituteMeal
    ) {
        $mealSizes = $meal
            ->sizes()
            ->withTrashed()
            ->get();

        foreach ($params->getSizeMapping() as $sizeId => $subSizeId) {
            if (!$mealSizes->contains($sizeId)) {
                throw new InvalidMappingException(
                    sprintf(
                        'Size %d doesn\'t exist in meal %d',
                        $sizeId,
                        $meal->id
                    )
                );
            }

            if (!$substituteMeal->sizes->contains($subSizeId)) {
                throw new InvalidMappingException(
                    sprintf(
                        'Substitute size %d doesn\'t exist in substitue meal %d',
                        $sizeId,
                        $meal->id
                    )
                );
            }
        }
    }

    protected function verifyAddonMapping(
        MealReplacementParams $params,
        Meal $meal,
        Meal $substituteMeal
    ) {
        $mealAddons = $meal
            ->addons()
            ->withTrashed()
            ->get();

        foreach ($params->getAddonMapping() as $addonId => $subAddonId) {
            if (!$mealAddons->contains($addonId)) {
                throw new InvalidMappingException(
                    sprintf(
                        'Addon %d doesn\'t exist in meal %d',
                        $addonId,
                        $meal->id
                    )
                );
            }

            if (!$substituteMeal->addons->contains($subAddonId)) {
                throw new InvalidMappingException(
                    sprintf(
                        'Substitute addon %d doesn\'t exist in substitue meal %d',
                        $subAddonId,
                        $meal->id
                    )
                );
            }
        }
    }

    protected function verifyComponentOptionMapping(
        MealReplacementParams $params,
        Meal $meal,
        Meal $substituteMeal
    ) {
        $mealComponentOptions = $meal
            ->componentOptions()
            ->withTrashed()
            ->get();

        foreach (
            $params->getComponentOptionMapping()
            as $optionId => $subOptionId
        ) {
            if (!$mealComponentOptions->contains($optionId)) {
                throw new InvalidMappingException(
                    sprintf(
                        'Component option %d doesn\'t exist in meal %d',
                        $optionId,
                        $meal->id
                    )
                );
            }

            if (!$substituteMeal->componentOptions->contains($subOptionId)) {
                throw new InvalidMappingException(
                    sprintf(
                        'Substitute component option %d doesn\'t exist in substitue meal %d',
                        $subOptionId,
                        $meal->id
                    )
                );
            }
        }
    }

    protected function activateSubstitute(Meal $substituteMeal)
    {
        if (!$substituteMeal->active) {
            $substituteMeal->active = 1;
            $substituteMeal->update();
        }
    }

    protected function replaceMealInPackages(
        MealReplacementParams $params,
        MealReplacementResult &$result
    ) {
        $this->replaceMealInPackageMeals($params, $result);
        $this->replaceMealInPackageSizes($params, $result);
        $this->replaceMealInPackageComponentOptions($params, $result);
        $this->replaceMealInPackageAddons($params, $result);
    }

    protected function replaceMealInPackageMeals(
        MealReplacementParams $params,
        MealReplacementResult &$result
    ) {
        $mealMealPackages = MealMealPackage::where(
            'meal_id',
            $params->getMealId()
        )->get();

        foreach ($mealMealPackages as $mealMealPackage) {
            $sizeId = $mealMealPackage->meal_size_id;
            $substituteSizeId = $sizeId
                ? $params->getSizeSubstitute($sizeId)
                : null;

            $existingMealMealPackage = MealMealPackage::where([
                'meal_id' => $params->getSubstituteMealId(),
                'meal_size_id' => $substituteSizeId,
                'meal_package_id' => $mealMealPackage->meal_package_id
            ])->first();

            if ($existingMealMealPackage) {
                $quantity =
                    $existingMealMealPackage->quantity +
                    $mealMealPackage->quantity;
                $existingMealMealPackage->quantity = $quantity;
                $existingMealMealPackage->update();
                $mealMealPackage->delete();

                $result->packageMeals->push($existingMealMealPackage->id);
            } else {
                $mealMealPackage->meal_id = $params->getSubstituteMealId();

                if ($params->hasSizeSubstitute($sizeId)) {
                    $mealMealPackage->meal_size_id = $substituteSizeId;
                }
                $mealMealPackage->update();
                $result->packageMeals->push($mealMealPackage->id);
            }
        }
    }

    protected function replaceMealInPackageSizes(
        MealReplacementParams $params,
        MealReplacementResult &$result
    ) {
        $mealMealPackageSizes = MealMealPackageSize::where(
            'meal_id',
            $params->getMealId()
        )->get();

        foreach ($mealMealPackageSizes as $mealMealPackageSize) {
            $sizeId = $mealMealPackageSize->meal_size_id;
            $substituteSizeId = $sizeId
                ? $params->getSizeSubstitute($sizeId)
                : null;

            $existingMealMealPackageSize = MealMealPackageSize::where([
                'meal_id' => $params->getSubstituteMealId(),
                'meal_size_id' => $substituteSizeId,
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
                $result->packageMealSizes->push(
                    $existingMealMealPackageSize->id
                );
            } else {
                $mealMealPackageSize->meal_id = $params->getSubstituteMealId();

                if ($params->hasSizeSubstitute($sizeId)) {
                    $mealMealPackageSize->meal_size_id = $substituteSizeId;
                }
                $mealMealPackageSize->update();
                $result->packageMealSizes->push($mealMealPackageSize->id);
            }
        }
    }

    protected function replaceMealInPackageComponentOptions(
        MealReplacementParams $params,
        MealReplacementResult &$result
    ) {
        $mealMealPackageComponentOptions = MealMealPackageComponentOption::Where(
            'meal_id',
            $params->getMealId()
        )->get();

        foreach (
            $mealMealPackageComponentOptions
            as $mealMealPackageComponentOption
        ) {
            $sizeId = $mealMealPackageComponentOption->meal_size_id;
            $substituteSizeId = $sizeId
                ? $params->getSizeSubstitute($sizeId)
                : null;

            $existingMealMealPackageComponentOption = MealMealPackageComponentOption::where(
                [
                    'meal_id' => $params->getSubstituteMealId(),
                    'meal_size_id' => $substituteSizeId,
                    'meal_package_component_option_id' =>
                        $mealMealPackageComponentOption->meal_package_component_option_id
                ]
            )->first();
            if ($existingMealMealPackageComponentOption) {
                // $quantity =
                //     $existingMealMealPackageComponentOption->quantity +
                //     $mealMealPackageComponentOption->quantity;
                // $existingMealMealPackageComponentOption->quantity = $quantity;
                // $existingMealMealPackageComponentOption->update();
                // $mealMealPackageComponentOption->delete();
                // $result->packageMealComponentOptions->push(
                //     $existingMealMealPackageComponentOption->id
                // );
            } else {
                $mealMealPackageComponentOption->meal_id = $params->getSubstituteMealId();

                if ($params->hasSizeSubstitute($sizeId)) {
                    $mealMealPackageComponentOption->meal_size_id = $substituteSizeId;
                }
                $mealMealPackageComponentOption->update();

                // Add to result
                $result->packageMealComponentOptions->push(
                    $mealMealPackageComponentOption->id
                );
            }
        }
    }

    protected function replaceMealInPackageAddons(
        MealReplacementParams $params,
        MealReplacementResult &$result
    ) {
        $mealMealPackageAddons = MealMealPackageAddon::where(
            'meal_id',
            $params->getMealId()
        )->get();

        foreach ($mealMealPackageAddons as $mealMealPackageAddon) {
            $sizeId = $mealMealPackageAddon->meal_size_id;
            $substituteSizeId = $sizeId
                ? $params->getSizeSubstitute($sizeId)
                : null;

            $existingMealMealPackageAddon = MealMealPackageAddon::where([
                'meal_id' => $params->getSubstituteMealId(),
                'meal_size_id' => $substituteSizeId,
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

                // Add to result
                $result->packageMealAddons->push(
                    $existingMealMealPackageAddon->id
                );
            } else {
                $mealMealPackageAddon->meal_id = $params->getSubstituteMealId();

                if ($params->hasSizeSubstitute($sizeId)) {
                    $mealMealPackageAddon->meal_size_id = $substituteSizeId;
                }

                $mealMealPackageAddon->update();

                // Add to result
                $result->packageMealAddons->push($mealMealPackageAddon->id);
            }
        }
    }

    protected function replaceMealInSubscriptions(
        MealReplacementParams $params,
        MealReplacementResult &$result
    ) {
        $subscriptionMeals = MealSubscription::where([
            ['meal_id', $params->getMealId()]
        ])
            ->whereHas('subscription', function ($query) {
                $query->whereIn('status', ['active', 'paused']);
            })
            ->get();

        foreach ($subscriptionMeals as $subscriptionMeal) {
            $this->replaceMealInSubscriptionMeal(
                $params,
                $result,
                $subscriptionMeal
            );
            $subscriptionMeal->subscription->syncPrices();
        }
    }

    protected function replaceMealInSubscriptionMeal(
        MealReplacementParams $params,
        MealReplacementResult &$result,
        MealSubscription $subscriptionMeal
    ) {
        $mealId = $params->getMealId();
        $meal = Meal::findOrFail($mealId);
        $substituteMealId = $params->getSubstituteMealId();
        $substituteMeal = Meal::findOrFail($substituteMealId);
        $subSizeId = null;

        if (!$subscriptionMeal->subscription) {
            throw new MissingSubscriptionForMealException(
                sprintf(
                    'MealSubscription %d doesn\'t have an associated subscription',
                    $subscriptionMeal->id
                )
            );
        }

        $newSubscriptionMealPrice = $substituteMeal->price;

        // Substitute size
        if ($subscriptionMeal->meal_size_id) {
            $subSizeId = $params->getSizeSubstitute(
                $subscriptionMeal->meal_size_id
            );
            $subSize = MealSize::findOrFail($subSizeId);

            $subscriptionMeal->last_meal_size_id =
                $subscriptionMeal->meal_size_id;
            $subscriptionMeal->meal_size_id = $subSizeId;
            $newSubscriptionMealPrice = $subSize->price;
        }

        // Substitute components
        foreach ($subscriptionMeal->components as $component) {
            $this->replaceMealInSubscriptionMealComponent(
                $params,
                $result,
                $component,
                $newSubscriptionMealPrice
            );
        }

        // Substitute addons
        foreach ($subscriptionMeal->addons as $addon) {
            $this->replaceMealInSubscriptionMealAddon(
                $params,
                $result,
                $addon,
                $newSubscriptionMealPrice
            );
        }

        $subscriptionMeal->last_meal_id = $subscriptionMeal->meal_id;
        $subscriptionMeal->meal_id = $substituteMealId;
        // $subscriptionMeal->price = !$subscriptionMeal->meal_package_subscription_id
        //     ? $newSubscriptionMealPrice * $subscriptionMeal->quantity
        //     : 0;

        $existingSubscriptionMeal = MealSubscription::where([
            ['meal_id', $substituteMealId],
            ['meal_size_id', $subSizeId],
            ['delivery_date', $subscriptionMeal->delivery_date],
            ['subscription_id', $subscriptionMeal->subscription_id]
        ])->first();

        if ($existingSubscriptionMeal) {
            $quantity =
                $existingSubscriptionMeal->quantity +
                $subscriptionMeal->quantity;
            $unitPrice =
                $existingSubscriptionMeal->price /
                $existingSubscriptionMeal->quantity;
            $existingSubscriptionMeal->quantity = $quantity;
            // $existingSubscriptionMeal->price = !$subscriptionMeal->meal_package_subscription_id
            //     ? $unitPrice * $quantity
            //     : 0;
            $existingSubscriptionMeal->delivery_date =
                $subscriptionMeal->delivery_date;
            $existingSubscriptionMeal->update();
            $subscriptionMeal->delete();
            $existingSubscriptionMeal->subscription->syncPrices(true);
        } else {
            $subscriptionMeal->save();
            $subscriptionMeal->fresh()->subscription->syncPrices(true);
        }
    }

    protected function replaceMealInSubscriptionMealComponent(
        MealReplacementParams $params,
        MealReplacementResult &$result,
        MealSubscriptionComponent $component,
        float &$newSubscriptionMealPrice
    ) {
        $subscriptionMeal = $component->mealSubscription;
        $option = $component->option;

        if (!$params->hasComponentOptionSubstitute($option->id)) {
            // We have no replacement for this component.
            // Was the component or option removed from the meal after the subscription created?
            // Delete it from the subscription
            $component->delete();

            Log::error('No component option substitute provided', [
                'subscription_id' => $subscriptionMeal->subscription_id,
                'meal_id' => $subscriptionMeal->meal_id,
                'substitute_meal_id' => $params->getSubstituteMealId(),
                'meal_component_id' => $component->meal_component_id,
                'meal_component_option_id' =>
                    $component->meal_component_option_id,
                'meal_subscription_id' => $subscriptionMeal->id
            ]);

            return;
        }

        $substituteOptionId = $params->getComponentOptionSubstitute(
            $option->id
        );
        $substituteOption = MealComponentOption::findOrFail(
            $substituteOptionId
        );

        // Update IDs
        $component->last_meal_component_id = $component->meal_component_id;
        $component->last_meal_component_option_id =
            $component->meal_component_option_id;
        $component->meal_component_id = $substituteOption->meal_component_id;
        $component->meal_component_option_id = $substituteOptionId;

        // Add to new price
        $newSubscriptionMealPrice += $substituteOption->price;

        // Save
        try {
            $component->save();
        } catch (\Exception $e) {
            if ($e->getCode() === '23000') {
                // already has this component. Skip
                Log::debug('Meal subscription already has component option', [
                    'subscription_id' => $subscriptionMeal->subscription_id,
                    'meal_id' => $subscriptionMeal->meal_id,
                    'substitute_meal_id' => $params->getSubstituteMealId(),
                    'meal_component_id' => $component->meal_component_id,
                    'meal_component_option_id' =>
                        $component->meal_component_option_id,
                    'meal_subscription_id' => $subscriptionMeal->id
                ]);
                $component->delete();
            }
        }
    }

    protected function replaceMealInSubscriptionMealAddon(
        MealReplacementParams $params,
        MealReplacementResult &$result,
        MealSubscriptionAddon $addon,
        float &$newSubscriptionMealPrice
    ) {
        $subscriptionMeal = $addon->mealSubscription;
        $originalMealAddonId = $addon->meal_addon_id;

        if ($params->hasAddonSubstitute($addon->meal_addon_id)) {
            $subAddonId = $params->getAddonSubstitute($originalMealAddonId);
            $subAddon = MealAddon::findOrFail($subAddonId);

            $addon->last_meal_addon_id = $addon->meal_addon_id;
            $addon->meal_addon_id = $subAddonId;

            $newSubscriptionMealPrice += $subAddon->price;

            try {
                $addon->save();
            } catch (\Exception $e) {
                if ($e->getCode() === '23000') {
                    // already has this component. Skip
                    Log::debug('Meal subscription already has addon', [
                        'subscription_id' => $subscriptionMeal->subscription_id,
                        'meal_id' => $subscriptionMeal->meal_id,
                        'substitute_meal_id' => $params->getSubstituteMealId(),
                        'meal_addon_id' => $originalMealAddonId,
                        'sub_meal_addon_id' => $subAddonId,
                        'meal_subscription_id' => $subscriptionMeal->id
                    ]);
                    // $addon->delete();
                }
            }
        } else {
            // We have no replacement for this addon.
            // Was the addon removed from the meal after the subscription created?
            // Delete it
            $addon->delete();

            Log::error('No addon substitute provided', [
                'subscription_id' => $subscriptionMeal->subscription_id,
                'meal_id' => $subscriptionMeal->meal_id,
                'substitute_meal_id' => $params->getSubstituteMealId(),
                'meal_addon_id' => $addon->meal_addon_id,
                'meal_subscription_id' => $subscriptionMeal->id
            ]);
        }
    }
}
