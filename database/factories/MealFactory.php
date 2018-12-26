<?php

use Faker\Generator as Faker;


$factory->define(App\Meal::class, function (Faker $faker) {
    return [
    	'active' => 1,
    	'store_id' => 1,
        'featured_image' => $faker->imageUrl($width = 100, $height = 100),
        'title' => $faker->sentence($nbWords = 3, $variableNbWords = true),
        'description' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
        'price' => mt_rand(80, 120) / 10,
        'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')->format('Y-m-d H:i:s')
    ];
});

