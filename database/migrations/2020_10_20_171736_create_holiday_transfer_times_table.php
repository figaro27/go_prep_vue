<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHolidayTransferTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('holiday_transfer_times', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_id');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table->datetime('holiday_date')->nullable();
            $table->time('pickupStartTime')->nullable();
            $table->time('pickupEndTime')->nullable();
            $table->time('deliveryStartTime')->nullable();
            $table->time('deliveryEndTime')->nullable();
            $table->boolean('transferTimeRange')->default(0);
            $table->unsignedInteger('transferTimeMinutesInterval')->default(30);
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
        Schema::dropIfExists('holiday_transfer_times');
    }
}
