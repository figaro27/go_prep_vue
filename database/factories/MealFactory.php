<?php

use Faker\Generator as Faker;


$factory->define(App\Meal::class, function (Faker $faker) {
	// $image = array(
	// 	"http://dev.goprep.com/images/meal-1.jpg",
	// 	"http://dev.goprep.com/images/meal-2.jpg",
	// 	"http://dev.goprep.com/images/meal-3.jpg",
	// 	"http://dev.goprep.com/images/meal-4.jpg",
	// 	"http://dev.goprep.com/images/meal-5.jpg",
	// 	"http://dev.goprep.com/images/meal-6.jpg",
	// 	"http://dev.goprep.com/images/meal-7.jpg",
	// 	"http://dev.goprep.com/images/meal-8.jpg"
	// );

	$mealTitles = [
		'Mediterranean Burger',
		'Texas Turkey Burger',
		'Mango Chicken',
		'BBQ Pulled Chicken',
		'Smoked BBQ Brisket',
		'Buffalo Chicken Pizza',
		'Pulled Chicken Burrito',
		'BBQ Grilled Steak',
		'Thai Chili Salmon',
		'Sesame Ginger Tilapia',
		'Chicken Caprese',
		'Ground Turkey Lasagna',
		'Keto Thai Chili Salmon',
		'Keto Chicken & Veggies',
		'Keto Texan Burger',
		'Cinnamon Banana French Toast',
		'Strawberry Protein Pancakes',
		'Breakfast Burrito Egg Whites',
		'Blueberry Protein Muffins',
		'Triple Chocolate Protein Brownies'
	];

	$mealImages = [
		'/images/store/meals/mediterranean-burger.jpg',
		'/images/store/meals/texas-turkey-burger.jpg',
		'/images/store/meals/mango-chicken.jpg',
		'/images/store/meals/bbq-pulled-chicken.jpg',
		'/images/store/meals/smoked-bbq-brisket.jpg',
		'/images/store/meals/buffalo-chicken-pizza.jpg',
		'/images/store/meals/pulled-chicken-burrito.jpg',
		'/images/store/meals/bbq-grilled-steak.jpg',
		'/images/store/meals/thai-chili-salmon.jpg',
		'/images/store/meals/sesame-chicken-tilapia.jpg',
		'/images/store/meals/chicken-caprese.jpg',
		'/images/store/meals/ground-turkey-lasagna.jpg',
		'/images/store/meals/keto-thai-chili-salmon.jpg',
		'/images/store/meals/keto-chicken-veggies.jpg',
		'/images/store/meals/keto-texan-burger.jpg',
		'/images/store/meals/cinnamon-french-toast.jpg',
		'/images/store/meals/strawberry-protein-pancakes.jpg',
		'/images/store/meals/breakfast-burrito-egg-whites.jpg',
		'/images/store/meals/blueberry-protein-muffins.jpg',
		'/images/store/meals/protein-brownies.jpg',
	];

	$rand = rand(0,19);
	
    return [
    	'active' => 1,
    	'store_id' => 1,
        'featured_image' => $mealImages[$rand],
        'title' => $mealTitles[$rand],
        'description' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
        'price' => mt_rand(80, 120) / 10,
        'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')->format('Y-m-d H:i:s')
    ];
});