<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeliveryDayMealPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_day_meal_packages', function (
            Blueprint $table
        ) {
            $table->increments('id');
            $table
                ->integer('delivery_day_id')
                ->references('id')
                ->on('delivery_days');
            $table
                ->integer('meal_package_id')
                ->references('id')
                ->on('meal_packages');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_day_meal_packages');
    }
}
