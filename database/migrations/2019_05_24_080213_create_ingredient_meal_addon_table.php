<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIngredientMealAddonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingredient_meal_addon', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ingredient_id')->unsigned();
            $table->integer('meal_addon_id')->unsigned();
            $table->float('quantity');
            $table->string('quantity_unit');
            $table->string('quantity_unit_display');
            $table->timestamps();

            /*$table
            ->foreign('ingredient_id', 'ingredient_id')
            ->references('id')
            ->on('ingredients');*/

            $table
                ->foreign('meal_addon_id', 'meal_addon_id')
                ->references('id')
                ->on('meal_addons');

            $table->unique(
                ['ingredient_id', 'meal_addon_id', 'quantity_unit'],
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
        Schema::drop('ingredient_meal_addon');
    }
}
