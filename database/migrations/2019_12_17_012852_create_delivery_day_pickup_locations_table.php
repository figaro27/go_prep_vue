<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryDayPickupLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_day_pickup_locations', function (
            Blueprint $table
        ) {
            $table->increments('id');
            $table
                ->integer('delivery_day_id')
                ->references('id')
                ->on('delivery_days');
            $table
                ->integer('pickup_location_id')
                ->references('id')
                ->on('pickup_locations');
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
        Schema::dropIfExists('delivery_day_pickup_locations');
    }
}
