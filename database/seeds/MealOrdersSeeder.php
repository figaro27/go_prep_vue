<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MealOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($order = 1; $order <= 50; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 1,
                        'order_id' => $order,
                        'meal_id' => rand(1, 20),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                } catch (\Exception $e) {}
            }
        }
    }
}