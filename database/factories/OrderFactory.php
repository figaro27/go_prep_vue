<?php

use Faker\Generator as Faker;

$factory->define(App\Order::class, function (Faker $faker) {
    return [
        'order_number' => uniqid(rand(100, 999), false),
        'store_id' => 1,
        'fulfilled' => rand(0, 1),
        'amount' => rand(100, 200),
        'delivery_date' => $faker->dateTimeBetween($startDate = '-1 week', $endDate = '+7 days'),
        'created_at' => $faker->dateTimeBetween($startDate = '-1 week', $endDate = 'now'),
    ];
});
