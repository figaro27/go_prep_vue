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

        for ($order = 201; $order <= 220; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 11,
                        'order_id' => $order,
                        'meal_id' => rand(231, 253),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 221; $order <= 240; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 12,
                        'order_id' => $order,
                        'meal_id' => rand(254, 276),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 241; $order <= 260; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 13,
                        'order_id' => $order,
                        'meal_id' => rand(277, 299),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 262; $order <= 280; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 14,
                        'order_id' => $order,
                        'meal_id' => rand(300, 322),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 281; $order <= 300; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 15,
                        'order_id' => $order,
                        'meal_id' => rand(323, 345),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 301; $order <= 320; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 16,
                        'order_id' => $order,
                        'meal_id' => rand(346, 368),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 321; $order <= 340; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 17,
                        'order_id' => $order,
                        'meal_id' => rand(369, 391),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 341; $order <= 360; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 18,
                        'order_id' => $order,
                        'meal_id' => rand(392, 414),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 361; $order <= 380; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 19,
                        'order_id' => $order,
                        'meal_id' => rand(415, 437),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 381; $order <= 400; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 20,
                        'order_id' => $order,
                        'meal_id' => rand(438, 460),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 401; $order <= 420; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 21,
                        'order_id' => $order,
                        'meal_id' => rand(461, 483),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 421; $order <= 440; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 22,
                        'order_id' => $order,
                        'meal_id' => rand(484, 506),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 441; $order <= 460; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 23,
                        'order_id' => $order,
                        'meal_id' => rand(507, 529),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 461; $order <= 480; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 24,
                        'order_id' => $order,
                        'meal_id' => rand(530, 552),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 481; $order <= 500; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 25,
                        'order_id' => $order,
                        'meal_id' => rand(553, 575),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 501; $order <= 520; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 26,
                        'order_id' => $order,
                        'meal_id' => rand(576, 598),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 521; $order <= 540; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 27,
                        'order_id' => $order,
                        'meal_id' => rand(599, 621),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 541; $order <= 560; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 28,
                        'order_id' => $order,
                        'meal_id' => rand(622, 644),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 561; $order <= 580; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 29,
                        'order_id' => $order,
                        'meal_id' => rand(645, 667),
                        'quantity' => rand(1, 4),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        for ($order = 581; $order <= 600; $order++) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    DB::table('meal_orders')->insert([
                        'store_id' => 30,
                        'order_id' => $order,
                        'meal_id' => rand(668, 690),
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
