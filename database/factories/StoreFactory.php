<?php

use Faker\Generator as Faker;

$factory->define(App\Store::class, function (Faker $faker) {
	static $i = 1;
    return [
      'user_id' => $i++,
      'accepted_toa' => 1,
      'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')
    ];
});
