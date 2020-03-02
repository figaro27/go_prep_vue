<?php

use App\User;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        $sunday = [
            date('Y-m-d', strtotime('next sunday')),
            date('Y-m-d', strtotime('next sunday')),
            date('Y-m-d', strtotime('next sunday')),
            date('Y-m-d', strtotime('next sunday')),
            date('Y-m-d', strtotime('sunday next week'))
        ];

        // for ($i = 31; $i <= 50; $i++) {
        //     DB::table('orders')->insert([
        //         'user_id' => $i,
        //         'customer_id' => $i - 10,
        //         'store_id' => 1,
        //         'order_number' => strtoupper(substr(sha1(uniqid()), 0, 8)),
        //         'fulfilled' => 0,
        //         'preFeePreDiscount' => rand(90, 190),
        //         'afterDiscountBeforeFees' => rand(80, 180),
        //         'processingFee' => rand(1, 4),
        //         'deliveryFee' => rand(1, 6),
        //         'salesTax' => rand(5, 10),
        //         'amount' => rand(100, 200),
        //         'delivery_date' => $sunday[rand(0, 4)],
        //         'created_at' => $faker->dateTimeBetween(
        //             $startDate = '-6 days',
        //             $endDate = 'now'
        //         ),
        //         'paid' => 1
        //     ]);
        // }

        $c = 0;
        for ($u = 1; $u <= 30; $u++) {
            for ($i = 31; $i <= 50; $i++) {
                DB::table('orders')->insert([
                    'user_id' => $i,
                    'customer_id' => $i + $c,
                    'store_id' => $u,
                    'order_number' => strtoupper(substr(sha1(uniqid()), 0, 8)),
                    'fulfilled' => 0,
                    'preFeePreDiscount' => rand(90, 190),
                    'afterDiscountBeforeFees' => rand(80, 180),
                    'processingFee' => rand(1, 4),
                    'deliveryFee' => rand(1, 6),
                    'salesTax' => rand(5, 10),
                    'amount' => rand(100, 200),
                    'originalAmount' => rand(100, 200),
                    'delivery_date' => $sunday[rand(0, 4)],
                    'created_at' => $faker->dateTimeBetween(
                        $startDate = '-6 days',
                        $endDate = 'now'
                    ),
                    'paid' => 1
                ]);
            }
            $c += 20;
        }

        // $c = 0;
        // for ($u = 2; $u <= 10; $u++) {
        //     for ($i = 50; $i <= 52; $i++) {
        //         DB::table('orders')->insert([
        //             'user_id' => $i - 10,
        //             'customer_id' => $i + $c - 10,
        //             'store_id' => $u,
        //             'order_number' => strtoupper(substr(sha1(uniqid()), 0, 8)),
        //             'fulfilled' => 0,
        //             'preFeePreDiscount' => rand(90, 190),
        //             'afterDiscountBeforeFees' => rand(80, 180),
        //             'processingFee' => rand(1, 4),
        //             'deliveryFee' => rand(1, 6),
        //             'salesTax' => rand(5, 10),
        //             'amount' => rand(100, 200),
        //             'delivery_date' => $faker->dateTimeBetween(
        //                 $startDate = '+30 days',
        //                 $endDate = '+33 days'
        //             ),
        //             'created_at' => $faker->dateTimeBetween(
        //                 $startDate = '+30 days',
        //                 $endDate = '+30 days'
        //             ),
        //             'paid' => 1
        //         ]);
        //     }
        //     $c += 20;
        // }
    }
}
