<?php

use Faker\Generator as Faker;

$factory->define(App\UserDetail::class, function (Faker $faker) {
    return [
    	'user_id' => rand(1,50),
    	'firstname' => $faker->firstName($gender = null),
    	'lastname' => $faker->lastName,
    	'phone' => $faker->phoneNumber,
    	'address' => $faker->address,
    	'city' => $faker->city,
    	'state' => $faker->stateAbbr,
    	'country' => 'USA',
    	'Delivery' => $faker->sentence($nbWords = 6, $variableNbWords = true)
    ];
});
