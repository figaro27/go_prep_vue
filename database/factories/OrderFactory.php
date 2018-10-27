<?php

use Faker\Generator as Faker;

$factory->define(App\Order::class, function (Faker $faker) {
    return [
    	'payment_method_id' => rand(1,300),
        'store_id' => rand(1,10),
        'delivery_status' => rand(1,5),
        'amount' => rand(100, 200),
        'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')
    ];
});
