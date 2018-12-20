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
            $table->integer('calories')->default(0);
            $table->integer('fatcalories')->default(0);
            $table->integer('totalfat')->default(0);
            $table->integer('satfat')->default(0);
            $table->integer('transfat')->default(0);
            $table->integer('cholesterol')->default(0);
            $table->integer('sodium')->default(0);
            $table->integer('totalcarb')->default(0);
            $table->integer('fibers')->default(0);
            $table->integer('sugars')->default(0);
            $table->integer('proteins')->default(0);
            $table->integer('vitamind')->default(0);
            $table->integer('potassium')->default(0);
            $table->integer('calcium')->default(0);
            $table->integer('iron')->default(0);
            $table->integer('addedsugars')->default(0);
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

