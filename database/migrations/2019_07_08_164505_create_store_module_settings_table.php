<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreModuleSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_module_settings', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->integer('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->integer('store_settings_id')
                ->references('id')
                ->on('store_settings');
            $table
                ->integer('store_modules_id')
                ->references('id')
                ->on('store_modules');
            $table->time('transferStartTime')->nullable();
            $table->time('transferEndTime')->nullable();
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
        Schema::dropIfExists('store_module_settings');
    }
}
