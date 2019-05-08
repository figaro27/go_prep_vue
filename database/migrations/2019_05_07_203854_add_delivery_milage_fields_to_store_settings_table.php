<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryMilageFieldsToStoreSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('deliveryFeeType')->nullable();
            $table->integer('mileageBase')->nullable();
            $table->integer('mileagePerMile')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('deliveryFeeType');
            $table->dropColumn('mileageBase');
            $table->dropColumn('mileagePerMile');
        });
    }
}
