<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealOrderComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_order_components', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->integer('meal_order_id')
                ->references('id')
                ->on('meal_orders');
            $table
                ->integer('meal_component_id')
                ->references('id')
                ->on('meal_components');
            $table
                ->integer('meal_component_option_id')
                ->references('id')
                ->on('meal_component_options');
            $table->timestamps();

            $table->unique(
                [
                    'meal_order_id',
                    'meal_component_id',
                    'meal_component_option_id'
                ],
                'uniq'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meal_order_components');
    }
}
