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

        for ($u = 1; $u <= 10; $u++) {
            for ($i = 11; $i <= 30; $i++) {
                DB::table('orders')->insert([
                    'user_id' => $i,
                    'customer_id' => $i,
                    'store_id' => $u,
                    'order_number' => strtoupper(substr(sha1(uniqid()), 0, 8)),
                    'fulfilled' => 0,
                    'amount' => rand(100, 200),
                    'delivery_date' => $sunday[rand(0, 4)],
                    'created_at' => $faker->dateTimeBetween(
                        $startDate = '-6 days',
                        $endDate = 'now'
                    ),
                    'paid' => 1
                ]);
            }
        }

        // Main test customer
        // $user = User::find(3);

        // if (!$user->hasStoreCustomer(1)) {
        //     $user->createStoreCustomer(1);
        // }
        // $customer = $user->getStoreCustomer(1, false);

        // for ($i = 0; $i < 10; $i++) {
        //     factory(App\Order::class)->create([
        //         'customer_id' => $customer->id,
        //         'user_id' => $user->id,
        //     ]);
        // }

        // // Others
        // $users = User::where([
        //     ['user_role_id', 1],
        //     ['id', '<>', 3],
        // ])->get();
    }
}
