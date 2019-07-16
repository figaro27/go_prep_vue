<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealMacrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_macros', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->unsignedInteger('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->unsignedInteger('meal_id')
                ->references('id')
                ->on('meals');
            $table
                ->unsignedInteger('meal_size_id')
                ->references('id')
                ->on('meal_sizes')
                ->nullable();
            $table->unsignedInteger('calories');
            $table->unsignedInteger('carbs');
            $table->unsignedInteger('protein');
            $table->unsignedInteger('fat');
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
        Schema::dropIfExists('meal_macros');
    }
}
