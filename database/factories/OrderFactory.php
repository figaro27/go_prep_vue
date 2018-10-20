<?php

use Faker\Generator as Faker;

$factory->define(App\Order::class, function (Faker $faker) {
    return [
        'user_id' => rand(4,53),
        'store_id' => rand(1,10),
        'delivery_status' => rand(1,5),
        'amount' => rand(50, 500),
        'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')
    ];
});
