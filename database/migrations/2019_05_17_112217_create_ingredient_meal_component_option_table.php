<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngredientMealComponentOptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingredient_meal_component_option', function (
            Blueprint $table
        ) {
            $table->increments('id');
            $table->integer('ingredient_id')->unsigned();
            $table->integer('meal_component_option_id')->unsigned();
            $table->float('quantity');
            $table->string('quantity_unit');
            $table->string('quantity_unit_display');
            $table->timestamps();

            $table
                ->foreign('ingredient_id', 'ingredient_id')
                ->references('id')
                ->on('ingredients');
            $table
                ->foreign('meal_component_option_id', 'option_id')
                ->references('id')
                ->on('meal_component_options');

            $table->unique(
                ['ingredient_id', 'meal_component_option_id', 'quantity_unit'],
                'distinct_ingredient'
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
        Schema::dropIfExists('ingredient_meal_component_option');
    }
}
