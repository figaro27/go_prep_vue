<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Meal;
use App\MealComponent;
use App\MealComponentOption;
use App\MealSize;
use App\MealSubscription;
use App\Store;
use App\Subscription;

class MealSubstitutionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $meals = [$this->createMeal(), $this->createMeal()];

        $subs = [$this->createSub([$meals[0]])];

        $subSizes = [
            $meals[0]->sizes[0]->id => $meals[1]->sizes[0]->id
        ];

        Meal::deleteMeal($meals[0]->id, $meals[1]->id, true, true, $subSizes);
    }

    public function createSub($meals)
    {
        Subscription::unguard();
        MealSubscription::unguard();

        $sub = Subscription::create([
            'user_id' => 1,
            'store_id' => 1,
            'status' => 'active'
        ]);

        foreach ($meals as $meal) {
            MealSubscription::create([
                'store_id' => 1,
                'subscription_id' => $sub->id,
                'meal_id' => $meal->id,
                'meal_size_id' => $meal->sizes[0]->id
            ]);
        }

        return $sub->fresh();
    }

    public function createMeal()
    {
        Meal::unguard();
        MealSize::unguard();
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
                'price' => 100,
                'multiplier' => 1
            ]),
            MealSize::create([
                'meal_id' => $meal->id,
                'title' => 'Medium',
                'price' => 100,
                'multiplier' => 1
            ]),
            MealSize::create([
                'meal_id' => $meal->id,
                'title' => 'Large',
                'price' => 100,
                'multiplier' => 1
            ])
        ];

        $cmps = [
            MealComponent::create([
                'store_id' => 1,
                'meal_id' => $meal->id,
                'meal_size_id' => null,
                'minimum' => 1,
                'maximum' => 3
            ])
        ];

        $opts = [
            MealComponentOption::create([
                'store_id' => 1,
                'meal_component_id' => $cmps[0]->id,
                'meal_size_id' => null,
                'price' => 10
            ])
        ];

        return $meal->fresh();
    }
}
