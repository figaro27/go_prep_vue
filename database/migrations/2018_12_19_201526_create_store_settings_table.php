<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id')->references('id')->on('stores');
            $table->integer('minimum')->default(5);
            $table->boolean('showNutrition')->default(true);
            $table->boolean('allowPickup')->default(false);
            $table->text('pickupInstructions')->nullable();
            $table->boolean('applyDeliveryFee')->default(false);
            $table->integer('deliveryFee')->nullable();
            $table->json('delivery_days');
            $table->enum('cutoff_day', [
              'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'
            ])->default('sun');
            $table->string('cutoff_time', 5)->default('00:00');
            $table->enum('delivery_distance_type', [
              'radius', 'zipcodes',
            ])->default('radius');
            $table->double('delivery_distance_radius')->nullable();
            $table->json('delivery_distance_zipcodes');
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
        Schema::dropIfExists('store_settings');
    }
}
