<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealMealTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_meal_tag', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('meal_id')->references('id')->on('meals');
            $table->integer('meal_tag_id')->references('id')->on('meal_tags');
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
        Schema::dropIfExists('meal_meal_tag');
    }
}
