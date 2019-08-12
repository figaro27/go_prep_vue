<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

use App\User;

class SubscriptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($u = 1; $u <= 30; $u++) {
            for ($i = 11; $i <= 20; $i++) {
                DB::table('subscriptions')->insert([
                    'user_id' => $i,
                    'customer_id' => $i,
                    'store_id' => $u,
                    'name' => 'name',
                    'status' => 'active',
                    'pickup' => 0,
                    'stripe_id' => substr(md5(rand()), 0, 13),
                    'stripe_customer_id' => 1,
                    'stripe_plan' => 1,
                    'quantity' => 1,
                    'amount' => mt_rand(1100, 2000) / 10,
                    'interval' => 'week',
                    'delivery_day' => rand(4, 7),
                    'created_at' => Carbon::now()->subDays($i),
                    'updated_at' => Carbon::now()
                ]);
            }
        }
    }
}
