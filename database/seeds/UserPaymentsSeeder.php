<?php

use Illuminate\Database\Seeder;

class UserPaymentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\UserPayment::class, 300)->create();
    }
}
