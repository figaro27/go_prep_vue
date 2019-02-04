<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SubscriptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=1;$i<=10;$i++)
            {
    	        DB::table('subscriptions')->insert([
    	        	'user_id' => rand(1,20),
    	        	'customer_id' => rand(1,20),
    	        	'store_id' => 1,
    	        	'name' => 'name',
    	        	'status' => 'active',
    	        	'stripe_id' => rand(111,999),
    	        	'stripe_customer_id' => 1,
    	        	'stripe_plan' => 1,
    	        	'quantity' => 1,
    	        	'amount' => mt_rand(80, 120) / 10,
    	        	'interval' => 1,
    	        	'delivery_day' => rand(1,7),
    	        	'created_at' => Carbon::now(),
    	            'updated_at' => Carbon::now()
    	        ]);
            }
    }
}
