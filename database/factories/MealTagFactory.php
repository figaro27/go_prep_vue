<?php

use Faker\Generator as Faker;

$factory->define(App\MealTag::class, function (Faker $faker) {
	
	$tag = array("Low Carb", "Low Calorie", "Vegan", "Breakfast");
    return [
        'tag' => $tag[rand(0,3)]
    ];
});


