<?php

use Faker\Generator as Faker;

$factory->define(App\UserPayment::class, function (Faker $faker) {
    return [
        'user_id' => rand(1, 50),
        'order_id' => rand(1,300),
        'amount' => rand(50, 500),
        'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')
    ];
});
