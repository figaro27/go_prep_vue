<?php

use Faker\Generator as Faker;

$factory->define(App\UserDetail::class, function (Faker $faker) {

    return [
    	'firstname' => $faker->firstName($gender = null),
    	'lastname' => $faker->lastName,
        'phone' => '(' . rand(333,444) . ') ' . rand(123,999) . '-' . rand(1234,9999),
        'address' => rand(123, 999) .' '. $faker->streetName,
    	'city' => $faker->city,
    	'state' => $faker->stateAbbr,
        'zip' => '0' . rand(7001,8989),
    	'country' => 'USA',
    	'Delivery' => $faker->sentence($nbWords = 6, $variableNbWords = true)
    ];
});
