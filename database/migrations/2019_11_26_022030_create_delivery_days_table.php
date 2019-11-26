<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_days', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->unsignedInteger('store_id')
                ->references('id')
                ->on('stores');
            $table->string('day');
            $table->string('type')->default('delivery');
            $table->text('instructions')->nullable();
            $table->string('cutoff_type')->default('timed');
            $table->integer('cutoff_days')->default(1);
            $table->integer('cutoff_hours')->default(0);
            $table->boolean('applyFee')->default(0);
            $table->decimal('fee')->nullable();
            $table->string('feeType')->nullable();
            $table->decimal('mileageBase')->nullable();
            $table->decimal('mileagePerMile')->nullable();
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
        Schema::dropIfExists('delivery_days');
    }
}
