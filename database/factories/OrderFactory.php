<?php

use Faker\Generator as Faker;

$factory->define(App\Order::class, function (Faker $faker) {

	$sunday = [
		date('Y-m-d', strtotime('next sunday')),
		date('Y-m-d', strtotime('next sunday')),
		date('Y-m-d', strtotime('next sunday')),
		date('Y-m-d', strtotime('next sunday')),
		date('Y-m-d', strtotime('sunday next week'))
	];

    return [
        'order_number' => strtoupper(substr(sha1(uniqid()), 0, 8)),
        'store_id' => 1,
        'fulfilled' => rand(0, 1),
        'amount' => rand(100, 200),
        'delivery_date' => $sunday[rand(0,4)],
        'created_at' => $faker->dateTimeBetween($startDate = '-6 days', $endDate = 'now'),
        'paid' => 1
    ];
});
