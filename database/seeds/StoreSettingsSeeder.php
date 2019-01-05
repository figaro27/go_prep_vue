<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StoreSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('store_settings')->insert([
	        	'store_id' => 1,
	            'minimum' => 5,
	            'delivery_days' => '["mon", "wed", "fri"]',
	            'cutoff_day' => 'sun',
	            'cutoff_time' => '00:00',
	            'delivery_distance_type' => 'radius',
	            'delivery_distance_radius' => 5,
	            'delivery_distance_zipcodes' => '[10000,10001,10002]',
	            'created_at' => Carbon::now(),
	            'updated_at' => Carbon::now()
	        ]);
    }
}
