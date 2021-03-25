<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Store;
use App\PackingSlipSetting;

class PackingSlipSettingsSeeder extends Seeder
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
            PackingSlipSetting::create([
                'store_id' => $store->id
            ]);
        }
    }
}
