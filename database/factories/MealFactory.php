<?php

use Faker\Generator as Faker;

$factory->define(App\Meal::class, function (Faker $faker) {
    return [
    	'store_id' => rand(1,10),
        'featured_image' => $faker->imageUrl($width = 100, $height = 100),
        'title' => $faker->sentence($nbWords = 3, $variableNbWords = true),
        'category' => $faker->word,
        'description' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
        'price' => rand(8, 12),
        'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')
    ];
});

