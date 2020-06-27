<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStoreIdToDeliveryDayMealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_day_meals', function (Blueprint $table) {
            $table
                ->unsignedInteger('store_id')
                ->references('id')
                ->on('stores')
                ->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_day_meals', function (Blueprint $table) {
            $table->dropColumn('store_id');
        });
    }
}
