<?php

use Faker\Generator as Faker;

$factory->define(App\Ingredient::class, function (Faker $faker) {
    $unitTypes = ['mass', 'volume', 'unit'];
    $food_names = ['Banana', 'Eggs', 'Sugar', 'Milk', 'Baking Powder', 'Oats', 'Bread', 'Breadcrumb', 'Chicken Breast', 'Tomatoes', 'Onion', 'Potatoes', 'Ground Turkey', 'Oregano', 'Celery', 'Carrots', 'Cream Cheese', 'Flour', 'Parsley', 'Penne'];
    $image = [
        '/images/store/ingredients/banana.jpg',
        '/images/store/ingredients/eggs.jpg',
        '/images/store/ingredients/sugar.jpg',
        '/images/store/ingredients/milk.jpg',
        '/images/store/ingredients/baking-powder.jpg',
        '/images/store/ingredients/oats.jpg',
        '/images/store/ingredients/bread.jpg',
        '/images/store/ingredients/breadcrumb.jpg',
        '/images/store/ingredients/chicken-breast.jpg',
        '/images/store/ingredients/tomatoes.jpg',
        '/images/store/ingredients/onion.jpg',
        '/images/store/ingredients/potatoes.jpg',
        '/images/store/ingredients/ground-turkey.jpg',
        '/images/store/ingredients/oregano.jpg',
        '/images/store/ingredients/celery.jpg',
        '/images/store/ingredients/carrots.jpg',
        '/images/store/ingredients/cream-cheese.jpg',
        '/images/store/ingredients/flour.jpg',
        '/images/store/ingredients/parsley.jpg',
        '/images/store/ingredients/penne.jpg',
    ];

    $rand = rand(0,19);


    return [
        'store_id' => 1,
        'image' => $faker->imageUrl($width = 1024, $height = 1024),
        // 'image_thumb' => $faker->imageUrl($width = 128, $height = 128),
        'food_name' => $food_names[$rand],
        'image_thumb' => $image[$rand],
        'unit_type' => $unitTypes[rand(0, 2)],
        'calories' => rand(10, 200),
        'fatcalories' => rand(10, 50),
        'totalfat' => rand(1, 50),
        'satfat' => rand(1, 20),
        'transfat' => rand(1, 10),
        'cholesterol' => rand(10, 50),
        'sodium' => rand(500, 1000),
        'totalcarb' => rand(5, 30),
        'fibers' => rand(5, 20),
        'sugars' => rand(1, 15),
        'proteins' => rand(20, 80),
        'vitamind' => rand(1, 10),
        'potassium' => rand(100, 300),
        'calcium' => rand(50, 200),
        'iron' => rand(1, 20),
        'addedsugars' => rand(1, 20),
    ];
});
