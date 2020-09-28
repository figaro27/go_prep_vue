<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStoreIdColumnToDeliveryDayMealPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_day_meal_packages', function (
            Blueprint $table
        ) {
            $table->unsignedInteger('store_id')->after('id');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_day_meal_packages', function (
            Blueprint $table
        ) {
            $table->dropColumn('store_id');
        });
    }
}
