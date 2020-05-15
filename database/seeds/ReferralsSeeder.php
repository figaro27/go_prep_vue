<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReferralsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($store = 1; $store <= 30; $store++) {
            for ($i = 40; $i < 50; $i++) {
                DB::table('referrals')->insert([
                    'store_id' => $store,
                    'user_id' => $i,
                    'ordersReferred' => rand(1, 5),
                    'amountReferred' => rand(100, 500),
                    'code' =>
                        'R' .
                        strtoupper(substr(uniqid(rand(10, 99), false), -3)) .
                        chr(rand(65, 90)) .
                        rand(0, 9),
                    'balance' => rand(100, 500),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        }
    }
}
