<?php

use Faker\Generator as Faker;

$factory->define(App\StoreDetail::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'phone' => $faker->phoneNumber,
        'address' => $faker->streetAddress,
        'city' => $faker->city,
        'state' => $faker->stateAbbr,
        'logo' => $faker->word,
        'domain' => $faker->domainWord,
        'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')
    ];
});