<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Meal;
use App\MealAddon;
use App\MealComponent;
use App\MealComponentOption;
use App\MealSize;
use App\MealSubscription;
use App\MealSubscriptionAddon;
use App\MealSubscriptionComponent;
use App\Store;
use App\Subscription;

class MealSubstitutionsTest extends TestCase
{
    //use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $meals = [$this->createMeal(), $this->createMeal()];

        $subs = [$this->createSub([$meals[0]])];

        $subMeals = [
            $meals[0]->id => $meals[1]->id
        ];

        $subSizes = collect([
            $meals[0]->sizes[0]->id => $meals[1]->sizes[0]->id,
            $meals[0]->sizes[1]->id => $meals[1]->sizes[1]->id
        ]);
        $subAddons = collect([
            $meals[0]->addons[0]->id => $meals[1]->addons[0]->id,
            $meals[0]->addons[1]->id => $meals[1]->addons[1]->id
        ]);
        $subComponentOptions = collect([
            $meals[0]->components[0]->options[0]->id =>
                $meals[1]->components[0]->options[0]->id,
            $meals[0]->components[0]->options[1]->id =>
                $meals[1]->components[0]->options[1]->id,
            $meals[0]->components[0]->options[2]->id =>
                $meals[1]->components[0]->options[2]->id
        ]);

        $subMealsBefore = MealSubscription::where(
            'subscription_id',
            $subs[0]->id
        )
            ->with(['addons', 'components', 'components'])
            ->get();

        Meal::deleteMeal(
            $meals[0]->id,
            $meals[1]->id,
            true,
            false,
            $subSizes,
            $subAddons,
            $subComponentOptions
        );

        $subMealsAfter = MealSubscription::where(
            'subscription_id',
            $subs[0]->id
        )
            ->with(['addons', 'components', 'components'])
            ->get();

        foreach ($subMealsBefore as $i => $mealBefore) {
            $mealAfter = $subMealsAfter[$i];
            $mealId = $mealBefore->meal_id;
            $subId = $subMeals[$mealId];

            $this->assertEquals($mealAfter->meal_id, $subId, 'Meal ID subbed');
            $this->assertEquals(
                $mealAfter->last_meal_id,
                $mealId,
                'Last meal ID saved'
            );

            foreach ($mealBefore->components as $i => $oldComponent) {
                $newComponent = $mealAfter->components[$i];

                $cmpId = $oldComponent->meal_component_id;
                $optId = $oldComponent->meal_component_option_id;

                $newOptId = $subComponentOptions[$optId];

                $this->assertEquals(
                    $newComponent->meal_component_option_id,
                    $newOptId,
                    'Option ID subbed'
                );
                $this->assertEquals(
                    $newComponent->last_meal_component_option_id,
                    $optId,
                    'Last option ID saved'
                );
            }

            foreach ($mealBefore->addons as $i => $oldAddon) {
                $newAddon = $mealAfter->addons[$i];

                $addonId = $oldAddon->meal_addon_id;

                $newAddonId = $subAddons[$addonId];

                $this->assertEquals(
                    $newAddon->meal_addon_id,
                    $newAddonId,
                    'Addon ID subbed'
                );
                $this->assertEquals(
                    $newAddon->last_meal_addon_id,
                    $addonId,
                    'Last addon ID saved'
                );
            }
        }
    }

    public function createSub($meals)
    {
        Subscription::unguard();
        MealSubscription::unguard();
        MealSubscriptionComponent::unguard();
        MealSubscriptionAddon::unguard();

        $sub = Subscription::create([
            'user_id' => 1,
            'customer_id' => 1,
            'store_id' => 1,
            'status' => 'active',
            'name' => '',
            'stripe_id' => '',
            'stripe_plan' => '',
            'stripe_customer_id' => '',
            'quantity' => 0,
            'amount' => 0,
            'interval' => 'week',
            'delivery_day' => 0
        ]);

        foreach ($meals as $meal) {
            $mealSub = MealSubscription::create([
                'store_id' => 1,
                'subscription_id' => $sub->id,
                'meal_id' => $meal->id,
                'meal_size_id' => $meal->sizes[0]->id
            ]);

            $mealSubCmp = MealSubscriptionComponent::create([
                'meal_subscription_id' => $mealSub->id,
                'meal_component_id' => $meal->components[0]->id,
                'meal_component_option_id' =>
                    $meal->components[0]->options[0]->id
            ]);

            $mealSubAddon = MealSubscriptionAddon::create([
                'meal_subscription_id' => $mealSub->id,
                'meal_addon_id' => $meal->addons[0]->id
            ]);
        }

        return $sub->fresh();
    }

    public function createMeal()
    {
        Meal::unguard();
        MealSize::unguard();
        MealAddon::unguard();
        MealComponent::unguard();
        MealComponentOption::unguard();

        $daysAgo = [Carbon::now()->subDays(30), Carbon::now()->subDays(25)];

        $meal = Meal::create([
            'active' => 1,
            'store_id' => 1,
            'price' => 100,
            'title' => '',
            'default_size_title' => 'Medium',
            'created_at' => 1
        ]);

        $sizes = [
            MealSize::create([
                'meal_id' => $meal->id,
                'title' => 'Small',
                'price' => 10,
                'multiplier' => 1
            ]),
            MealSize::create([
                'meal_id' => $meal->id,
                'title' => 'Large',
                'price' => 100,
                'multiplier' => 1
            ])
        ];

        $addons = [
            MealAddon::create([
                'store_id' => 1,
                'meal_id' => $meal->id,
                'meal_size_id' => null,
                'title' => '',
                'price' => 0
            ]),
            MealAddon::create([
                'store_id' => 1,
                'meal_id' => $meal->id,
                'meal_size_id' => $sizes[0]->id,
                'title' => '',
                'price' => 0
            ]),
            MealAddon::create([
                'store_id' => 1,
                'meal_id' => $meal->id,
                'meal_size_id' => $sizes[1]->id,
                'title' => '',
                'price' => 0
            ])
        ];

        $cmps = [
            MealComponent::create([
                'store_id' => 1,
                'meal_id' => $meal->id,
                'minimum' => 1,
                'maximum' => 3,
                'title' => ''
            ])
        ];

        $opts = [
            MealComponentOption::create([
                'store_id' => 1,
                'meal_component_id' => $cmps[0]->id,
                'meal_size_id' => null,
                'price' => 10,
                'title' => ''
            ]),
            MealComponentOption::create([
                'store_id' => 1,
                'meal_component_id' => $cmps[0]->id,
                'meal_size_id' => $sizes[0]->id,
                'price' => 20,
                'title' => ''
            ]),
            MealComponentOption::create([
                'store_id' => 1,
                'meal_component_id' => $cmps[0]->id,
                'meal_size_id' => $sizes[1]->id,
                'price' => 30,
                'title' => ''
            ])
        ];

        return $meal->fresh();
    }
}
