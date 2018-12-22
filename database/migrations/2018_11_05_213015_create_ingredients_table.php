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
            $table->double('calories')->default(0);
            $table->double('fatcalories')->default(0);
            $table->double('totalfat')->default(0);
            $table->double('satfat')->default(0);
            $table->double('transfat')->default(0);
            $table->double('cholesterol')->default(0);
            $table->double('sodium')->default(0);
            $table->double('totalcarb')->default(0);
            $table->double('fibers')->default(0);
            $table->double('sugars')->default(0);
            $table->double('proteins')->default(0);
            $table->double('vitamind')->default(0);
            $table->double('potassium')->default(0);
            $table->double('calcium')->default(0);
            $table->double('iron')->default(0);
            $table->double('addedsugars')->default(0);
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

