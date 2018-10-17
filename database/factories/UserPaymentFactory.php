<?php

use Faker\Generator as Faker;

$factory->define(App\UserPayment::class, function (Faker $faker) {
    return [
        'user_id' => rand(1, 50),
        'order_id' => rand(1,200),
        'amount' => rand(50, 500)
    ];
});
