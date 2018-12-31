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
	            'created_at' => Carbon::now(),
	            'updated_at' => Carbon::now()
	        ]);
    }
}
