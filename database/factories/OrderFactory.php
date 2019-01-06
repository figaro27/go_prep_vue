<?php

use Faker\Generator as Faker;

$factory->define(App\Order::class, function (Faker $faker) {
    return [
    	'order_number' => uniqid(rand(100,999), false),
        'store_id' => 1,
        'fulfilled' => rand(0,1),
        'amount' => rand(100, 200),
        'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')
    ];
});
