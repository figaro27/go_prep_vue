<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MealOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	for ($order=1;$order<=757;$order++)
    	{
	        DB::table('meal_orders')->insert([
	        	'store_id' => 1,
	            'order_id' => $order,
	            'meal_id' => rand(1,50),
	            'created_at' => Carbon::now(),
	            'updated_at' => Carbon::now()
	        ]);
    }
    }
}
