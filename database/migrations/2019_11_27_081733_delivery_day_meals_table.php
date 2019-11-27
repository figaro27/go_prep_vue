<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeliveryDayMealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_day_meals', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->integer('delivery_day_id')
                ->references('id')
                ->on('delivery_days');
            $table
                ->integer('meal_id')
                ->references('id')
                ->on('meals');
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
        Schema::dropIfExists('delivery_day_meals');
    }
}
