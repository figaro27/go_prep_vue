<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealMealPackageComponentOptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_meal_package_component_option', function (
            Blueprint $table
        ) {
            $table->increments('id');
            $table
                ->unsignedInteger('meal_package_component_option_id')
                ->references('id')
                ->on('meal_package_component_options');
            $table
                ->unsignedInteger('meal_id')
                ->references('id')
                ->on('meals');
            $table
                ->unsignedInteger('meal_size_id')
                ->references('id')
                ->on('meal_sizes')
                ->nullable();
            $table->unsignedInteger('quantity');
            $table->double('price', 6, 2);
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
        Schema::dropIfExists('meal_meal_package_components');
    }
}
