<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryGiftCardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_gift_card', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->integer('category_id')
                ->references('id')
                ->on('categories');
            $table
                ->integer('gift_card_id')
                ->references('id')
                ->on('gift_cards');
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
        Schema::dropIfExists('category_gift_card');
    }
}
