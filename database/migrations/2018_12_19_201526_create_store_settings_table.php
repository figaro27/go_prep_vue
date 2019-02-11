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
            $table->string('timezone')->default('EST');
            $table->boolean('open')->default(true);
            $table->text('closedReason')->nullable();
            $table->string('minimumOption');
            $table->integer('minimumMeals')->default(5)->nullable();
            $table->integer('minimumPrice')->default(50)->nullable();
            $table->boolean('showNutrition')->default(true);
            $table->boolean('allowPickup')->default(false);
            $table->text('pickupInstructions')->nullable();
            $table->boolean('applyMealPlanDiscount')->default(false);
            $table->integer('mealPlanDiscount')->nullable();
            $table->boolean('applyDeliveryFee')->default(false);
            $table->integer('deliveryFee')->nullable();
            $table->boolean('applyProcessingFee')->default(false);
            $table->integer('processingFee')->nullable();
            $table->longtext('delivery_days');
            $table->tinyInteger('cutoff_days')->unsigned()->default(1);
            $table->integer('cutoff_hours')->unsigned()->default(0);
            $table->enum('delivery_distance_type', [
              'radius', 'zipcodes',
            ])->default('radius');
            $table->double('delivery_distance_radius')->nullable();
            $table->longtext('delivery_distance_zipcodes');
            $table->string('stripe_id')->nullable();
            $table->longText('stripe_account')->nullable();
            $table->json('notifications');
            $table->integer('view_delivery_days')->nullable();
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
