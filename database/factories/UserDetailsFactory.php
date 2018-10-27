<?php

use Faker\Generator as Faker;

$factory->define(App\UserDetail::class, function (Faker $faker) {
    return [
    	'firstname' => $faker->firstName($gender = null),
    	'lastname' => $faker->lastName,
    	'phone' => $faker->phoneNumber,
    	'address' => $faker->streetAddress,
    	'city' => $faker->city,
    	'state' => $faker->stateAbbr,
    	'country' => 'USA',
    	'Delivery' => $faker->sentence($nbWords = 6, $variableNbWords = true)
    ];
});
