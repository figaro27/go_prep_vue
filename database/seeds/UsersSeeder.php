<?php

use Illuminate\Database\Seeder;
use Faker\Factory;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        // Creating Store Users
        for ($i = 1; $i <= 30; $i++) {
            DB::table('users')->insert([
                'user_role_id' => 2,
                'email' => 'store' . $i . '@goprepdemo.com',
                'email_verified_at' => now(),
                'password' => bcrypt('secret'),
                'remember_token' => str_random(10),
                'created_at' => now(),
                'updated_at' => now(),
                'accepted_tos' => 1
            ]);
        }

        // Creating Customer Users
        for ($i = 1; $i <= 20; $i++) {
            DB::table('users')->insert([
                'user_role_id' => 1,
                'email' => 'customer' . $i . '@goprepdemo.com',
                'email_verified_at' => now(),
                'password' => bcrypt('secret'),
                'remember_token' => str_random(10),
                'created_at' => now(),
                'updated_at' => now(),
                'accepted_tos' => 1
            ]);
        }

        // Creating Customers
        for ($u = 1; $u <= 10; $u++) {
            for ($i = 11; $i <= 30; $i++) {
                DB::table('customers')->insert([
                    'store_id' => $u,
                    'user_id' => $i,
                    'stripe_id' => 'cus_EpKhIQbBcstUvl',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
