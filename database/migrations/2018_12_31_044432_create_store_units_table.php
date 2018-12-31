<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_units', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id')->unsigned();
            $table->integer('ingredient_id')->unsigned();
            $table->string('unit');
            $table->timestamps();

            $table->unique(['store_id', 'ingredient_id']);

            $table->foreign('store_id')->references('id')->on('stores');
            $table->foreign('ingredient_id')->references('id')->on('ingredients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_units', function (Blueprint $table) {
            //$table->dropForeign('store_id');
            //$table->dropForeign('ingredient_id');
        });
        Schema::dropIfExists('store_units');
    }
}
