<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryDayIdToMealPackageComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_package_components', function (Blueprint $table) {
            $table
                ->integer('delivery_day_id')
                ->references('id')
                ->on('delivery_days')
                ->after('maximum')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meal_package_components', function (Blueprint $table) {
            $table->dropColumn('delivery_day_id');
        });
    }
}
