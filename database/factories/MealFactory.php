<?php

use Faker\Generator as Faker;


$factory->define(App\Meal::class, function (Faker $faker) {
	$image = array(
		"http://dev.goprep.com/images/meal-1.jpg",
		"http://dev.goprep.com/images/meal-2.jpg",
		"http://dev.goprep.com/images/meal-3.jpg",
		"http://dev.goprep.com/images/meal-4.jpg",
		"http://dev.goprep.com/images/meal-5.jpg",
		"http://dev.goprep.com/images/meal-6.jpg",
		"http://dev.goprep.com/images/meal-7.jpg",
		"http://dev.goprep.com/images/meal-8.jpg"
	);
	
    return [
    	'active' => 1,
    	'store_id' => 1,
        'featured_image' => $image[rand(0,7)],
        'title' => $faker->sentence($nbWords = 3, $variableNbWords = true),
        'description' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
        'price' => mt_rand(80, 120) / 10,
        'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')->format('Y-m-d H:i:s')
    ];
});