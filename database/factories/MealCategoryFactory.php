<?php

use Faker\Generator as Faker;


$factory->define(App\MealCategory::class, function (Faker $faker) {
	$category = array("Breakfast", "Lunch", "Dinner");
    return [
    	'meal_id' => rand(1,50),
        'category' => $category[rand(0,2)],
        'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')
    ];
});

