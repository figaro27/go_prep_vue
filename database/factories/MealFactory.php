<?php

use Faker\Generator as Faker;


$factory->define(App\Meal::class, function (Faker $faker) {
	$image = array(
		"http://store.goprep.localhost/storage/meal-1.jpg",
		"http://store.goprep.localhost/storage/meal-2.jpg",
		"http://store.goprep.localhost/storage/meal-3.jpg",
		"http://store.goprep.localhost/storage/meal-4.jpg",
		"http://store.goprep.localhost/storage/meal-5.jpg",
		"http://store.goprep.localhost/storage/meal-6.jpg",
		"http://store.goprep.localhost/storage/meal-7.jpg",
		"http://store.goprep.localhost/storage/meal-8.jpg"
	);
	
    return [
    	'active' => 1,
    	'store_id' => 1,
        'featured_image' => $image[rand(0,7)],
        'title' => $faker->sentence($nbWords = 3, $variableNbWords = true),
        'description' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
        'price' => mt_rand(80, 120) / 10,
        'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')
    ];
});