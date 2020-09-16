<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReportRecordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($store = 1; $store <= 30; $store++) {
            DB::table('report_records')->insert([
                'store_id' => $store,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
