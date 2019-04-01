<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MealSubscriptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($subscription = 1; $subscription <= 10; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 1,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(1, 20),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }
    }
}
