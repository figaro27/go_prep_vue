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
    	        DB::table('subscriptions')->insert([
    	        	'user_id' => 3,
    	        	'customer_id' => 3,
    	        	'store_id' => 1,
    	        	'name' => 'name',
    	        	'status' => 'active',
    	        	'stripe_id' => substr(md5(rand()), 0, 13),
    	        	'stripe_customer_id' => 1,
    	        	'stripe_plan' => 1,
    	        	'quantity' => 1,
    	        	'amount' => mt_rand(1200, 3000) / 10,
    	        	'interval' => 'week',
    	        	'delivery_day' => rand(1,7),
    	        	'created_at' => Carbon::now(),
    	            'updated_at' => Carbon::now()
    	        ]);

                for($i=13;$i<23;$i++){
                    DB::table('subscriptions')->insert([
                        'user_id' => $i,
                        'customer_id' => $i,
                        'store_id' => 1,
                        'name' => 'name',
                        'status' => 'active',
                        'stripe_id' => substr(md5(rand()), 0, 13),
                        'stripe_customer_id' => 1,
                        'stripe_plan' => 1,
                        'quantity' => 1,
                        'amount' => mt_rand(1200, 3000) / 10,
                        'interval' => 'week',
                        'delivery_day' => rand(4,7),
                        'created_at' => Carbon::now()->subDays($i),
                        'updated_at' => Carbon::now()
                    ]);
            }
            
            
    }
}
