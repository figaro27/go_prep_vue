<?php

use Faker\Generator as Faker;

$factory->define(App\Store::class, function (Faker $faker) {
    return [
    	'user_id' => 2,
        'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')
    ];
});
