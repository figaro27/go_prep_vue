<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryDayIdColumnToMealMealPackageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_meal_package', function (Blueprint $table) {
            $table
                ->unsignedInteger('delivery_day_id')
                ->references('id')
                ->on('delivery_days')
                ->after('quantity')
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
        Schema::table('meal_meal_package', function (Blueprint $table) {
            $table->dropColumn('delivery_day_id');
        });
    }
}
