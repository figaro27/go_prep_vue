<?php

use Faker\Generator as Faker;

$factory->define(App\MealCategory::class, function (Faker $faker) {
    return [
        'meal_id' => rand(1, 50),
        'store_category_id' => rand(0, 2),
        'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now'),
    ];
});
