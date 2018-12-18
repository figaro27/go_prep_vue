<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIngredientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id')->references('id')->on('stores');
            $table->string('food_name');
            $table->integer('serving_qty');
            $table->string('serving_unit');
            $table->integer('calories');
            $table->integer('fatcalories');
            $table->integer('totalfat');
            $table->integer('satfat');
            $table->integer('transfat');
            $table->integer('cholesterol');
            $table->integer('sodium');
            $table->integer('totalcarb');
            $table->integer('fibers');
            $table->integer('sugars');
            $table->integer('proteins');
            $table->integer('vitamind');
            $table->integer('potassium');
            $table->integer('calcium');
            $table->integer('iron');
            $table->integer('addedsugars');
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
        Schema::dropIfExists('ingredients');
    }
}

