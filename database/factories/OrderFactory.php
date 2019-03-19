<?php

use Faker\Generator as Faker;

$factory->define(App\Order::class, function (Faker $faker) {

    return [
        'order_number' => strtoupper(substr(sha1(uniqid()), 0, 8)),
        'store_id' => 1,
        'fulfilled' => rand(0, 1),
        'amount' => rand(100, 200),
        'delivery_date' => $faker->dateTimeBetween($startDate = '-2 days', $endDate = '+3 days'),
        'created_at' => $faker->dateTimeBetween($startDate = '-4 days', $endDate = 'now'),
        'paid' => 1
    ];
});
