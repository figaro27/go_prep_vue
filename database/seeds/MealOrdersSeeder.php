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
        for ($order = 1; $order <= 20; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 1,
                        'order_id' => $order,
                        'meal_id' => rand(1, 23),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 21; $order <= 40; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 2,
                        'order_id' => $order,
                        'meal_id' => rand(24, 46),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 41; $order <= 60; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 3,
                        'order_id' => $order,
                        'meal_id' => rand(47, 69),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 61; $order <= 80; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 4,
                        'order_id' => $order,
                        'meal_id' => rand(70, 92),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 81; $order <= 100; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 5,
                        'order_id' => $order,
                        'meal_id' => rand(93, 115),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 101; $order <= 120; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 6,
                        'order_id' => $order,
                        'meal_id' => rand(116, 138),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 121; $order <= 140; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 7,
                        'order_id' => $order,
                        'meal_id' => rand(139, 161),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 141; $order <= 160; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 8,
                        'order_id' => $order,
                        'meal_id' => rand(162, 184),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 161; $order <= 180; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 9,
                        'order_id' => $order,
                        'meal_id' => rand(185, 207),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 181; $order <= 200; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 10,
                        'order_id' => $order,
                        'meal_id' => rand(208, 230),
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
