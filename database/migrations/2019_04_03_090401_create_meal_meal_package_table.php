<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealMealPackageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_meal_package', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->unsignedInteger('meal_id')
                ->references('id')
                ->on('meals');
            $table
                ->unsignedInteger('meal_package_id')
                ->references('id')
                ->on('categories');
            $table->integer('quantity');
            $table->timestamps();

            $table->unique(['meal_id', 'meal_package_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meal_meal_package');
    }
}
