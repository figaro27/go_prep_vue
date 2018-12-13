<?php

use Faker\Generator as Faker;

$factory->define(App\MealTag::class, function (Faker $faker) {
	
	$tag = array("Low Carb", "Low Calorie", "Vegan", "Breakfast");
    
  $title = $tag[rand(0, 3)];
    return [
        'tag' => $title,
        'slug' => str_slug($title),
        'store_id' => 1,
    ];
});


