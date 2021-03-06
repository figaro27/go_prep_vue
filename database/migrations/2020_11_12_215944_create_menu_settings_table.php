<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_id');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table->unsignedInteger('image_columns')->default(4);
            $table->unsignedInteger('text_columns')->default(2);
            $table->boolean('hidePrice')->default(0);
            $table->string('spinner')->default('pan');
            $table->string('favicon')->default('default.png');
            $table->boolean('redirectToCheckout')->default(0);
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
        Schema::dropIfExists('menu_settings');
    }
}
