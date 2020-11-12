<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Store;
use App\MenuSetting;

class ReportRecordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stores = Store::all();
        foreach ($stores as $store) {
            MenuSetting::create([
                'store_id' => $store->id
            ]);
        }
    }
}
