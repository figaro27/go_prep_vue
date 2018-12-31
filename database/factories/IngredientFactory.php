<?php

use Faker\Generator as Faker;

$factory->define(App\Ingredient::class, function (Faker $faker) {
    $unitTypes = ['mass', 'volume', 'unit'];
    return [
        'store_id' => 1,
        'image' => $faker->imageUrl($width = 1024, $height = 1024),
        'image_thumb' => $faker->imageUrl($width = 128, $height = 128),
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
