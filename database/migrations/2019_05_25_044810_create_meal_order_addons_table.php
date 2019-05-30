<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealOrderAddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_order_addons', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->integer('meal_order_id')
                ->references('id')
                ->on('meal_orders');
            $table
                ->integer('meal_addon_id')
                ->references('id')
                ->on('meal_addons');

            $table->timestamps();

            $table->unique(['meal_order_id', 'meal_addon_id'], 'uniq');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meal_order_addons');
    }
}
