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

        for ($subscription = 101; $subscription <= 110; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 11,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(231, 253),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 111; $subscription <= 120; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 12,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(254, 276),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 121; $subscription <= 130; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 13,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(277, 290),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 131; $subscription <= 140; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 14,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(291, 313),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 141; $subscription <= 150; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 15,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(314, 336),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 151; $subscription <= 160; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 16,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(337, 359),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 161; $subscription <= 170; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 17,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(360, 382),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 171; $subscription <= 180; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 18,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(383, 405),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 181; $subscription <= 190; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 19,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(406, 428),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 191; $subscription <= 200; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 20,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(429, 451),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 201; $subscription <= 210; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 21,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(452, 474),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 211; $subscription <= 220; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 22,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(475, 497),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 221; $subscription <= 230; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 23,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(498, 520),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 231; $subscription <= 240; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 24,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(521, 543),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 241; $subscription <= 250; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 25,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(544, 566),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 251; $subscription <= 260; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 26,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(567, 589),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 261; $subscription <= 270; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 27,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(590, 612),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 271; $subscription <= 280; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 28,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(613, 635),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 281; $subscription <= 290; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 29,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(636, 658),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($subscription = 291; $subscription <= 300; $subscription++) {
            for ($i = 1; $i <= 6; $i++) {
                try {
                    DB::table('meal_subscriptions')->insert([
                        'store_id' => 30,
                        'subscription_id' => $subscription,
                        'meal_id' => rand(659, 681),
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
