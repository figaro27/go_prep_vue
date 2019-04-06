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
                        'meal_id' => rand(1, 23),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 11; $subscription <= 20; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 2,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(24, 46),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 21; $subscription <= 30; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 3,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(47, 69),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 31; $subscription <= 40; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 4,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(70, 92),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 41; $subscription <= 50; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 5,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(93, 115),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 51; $subscription <= 60; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 6,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(116, 138),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 61; $subscription <= 70; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 7,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(139, 161),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 71; $subscription <= 80; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 8,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(162, 184),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 81; $subscription <= 90; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 9,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(185, 207),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 91; $subscription <= 100; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 10,
                        'subscription_id' => $subscription,
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
