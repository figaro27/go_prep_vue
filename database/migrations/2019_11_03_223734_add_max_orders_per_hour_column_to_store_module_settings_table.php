<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaxOrdersPerHourColumnToStoreModuleSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_module_settings', function (Blueprint $table) {
            $table
                ->unsignedInteger('maxOrdersPerHour')
                ->after('transferTimeRange')
                ->default(10000);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_module_settings', function (Blueprint $table) {
            $table->dropColumn();
        });
    }
}
