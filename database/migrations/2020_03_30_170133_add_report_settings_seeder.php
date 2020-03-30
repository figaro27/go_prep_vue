<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Store;
use App\ReportSetting;

class AddReportSettingsSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $stores = Store::all();
        foreach ($stores as $store) {
            ReportSetting::create([
                'store_id' => $store->id,
                'lab_width' => 4.0,
                'lab_height' => 2.33
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
