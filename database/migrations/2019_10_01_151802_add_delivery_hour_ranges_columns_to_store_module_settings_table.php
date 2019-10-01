<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryHourRangesColumnsToStoreModuleSettingsTable extends Migration
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
                ->time('deliveryStartTime')
                ->after('transferEndTime')
                ->nullable();
            $table
                ->time('deliveryEndTime')
                ->after('deliveryStartTime')
                ->nullable();
            $table->renameColumn('transferStartTime', 'pickupStartTime');
            $table->renameColumn('transferEndTime', 'pickupEndTime');
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
            $table->dropColumn('deliveryStartTime');
            $table->dropColumn('deliveryEndTime');
            $table->renameColumn('pickupStartTime', 'transferStartTime');
            $table->renameColumn('pickupEndTime', 'transferEndTime');
        });
    }
}
