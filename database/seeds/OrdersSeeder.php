<?php

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
        DB::table('orders')->insert([
            'user_id' => 11,
            'payment_method_id' => rand(1,300),
	        'store_id' => rand(1,10),
	        'delivery_status' => rand(1,5),
	        'amount' => rand(100, 200),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('orders')->insert([
            'user_id' => 11,
            'payment_method_id' => rand(1,300),
	        'store_id' => rand(1,10),
	        'delivery_status' => rand(1,5),
	        'amount' => rand(100, 200),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('orders')->insert([
            'user_id' => 11,
            'payment_method_id' => rand(1,300),
	        'store_id' => rand(1,10),
	        'delivery_status' => rand(1,5),
	        'amount' => rand(100, 200),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('orders')->insert([
            'user_id' => 11,
            'payment_method_id' => rand(1,300),
	        'store_id' => rand(1,10),
	        'delivery_status' => rand(1,5),
	        'amount' => rand(100, 200),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('orders')->insert([
            'user_id' => 11,
            'payment_method_id' => rand(1,300),
	        'store_id' => rand(1,10),
	        'delivery_status' => rand(1,5),
	        'amount' => rand(100, 200),
            'created_at' => now(),
            'updated_at' => now()
        ]);


    }
}
