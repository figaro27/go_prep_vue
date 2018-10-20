<?php

use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // factory(App\UserPayment::class, 300)->create()->each(function($u) {
        //     $u->userPayment()->save(factory(App\UserPayment::class)->make());
        //   });

        factory(App\Order::class, 300)->create();
    }
}
