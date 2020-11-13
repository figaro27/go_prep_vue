<?php

use Illuminate\Database\Seeder;
use App\Store;
use App\MenuSetting;

class MenuSettingsSeeder extends Seeder
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
