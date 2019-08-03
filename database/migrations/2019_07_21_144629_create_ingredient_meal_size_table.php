<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIngredientMealSizeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingredient_meal_size', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ingredient_id')->unsigned();
            $table->integer('meal_size_id')->unsigned();
            $table->float('quantity');
            $table->string('quantity_unit');
            $table->string('quantity_unit_display');
            $table->timestamps();

            $table
                ->foreign('ingredient_id')
                ->references('id')
                ->on('ingredients');
            $table
                ->foreign('meal_size_id')
                ->references('id')
                ->on('meal_sizes');

            $table->unique(
                ['ingredient_id', 'meal_size_id', 'quantity_unit'],
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
        Schema::dropIfExists('ingredient_meal_size');
    }
}
