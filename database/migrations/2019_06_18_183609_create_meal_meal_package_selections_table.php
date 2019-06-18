<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealMealPackageSelectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_meal_package_selections', function (
            Blueprint $table
        ) {
            $table->increments('id');
            $table
                ->unsignedInteger('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->unsignedInteger('meal_package_id')
                ->references('id')
                ->on('meal_packages');
            $table
                ->unsignedInteger('meal_package_size_id')
                ->references('id')
                ->on('meal_package_sizes');
            $table
                ->unsignedInteger('meal_package_selection_id')
                ->references('id')
                ->on('meal_package_selections');
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
        Schema::dropIfExists('meal_meal_package_selections');
    }
}
