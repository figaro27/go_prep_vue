<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderLabelSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_label_settings', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->unsignedInteger('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table->boolean('customer')->default(0);
            $table->boolean('address')->default(0);
            $table->boolean('phone')->default(0);
            $table->boolean('delivery')->default(0);
            $table->boolean('order_number')->default(0);
            $table->boolean('order_date')->default(0);
            $table->boolean('delivery_date')->default(0);
            $table->boolean('amount')->default(0);
            $table->boolean('balance')->default(0);
            $table->boolean('daily_order_number')->default(0);
            $table->boolean('pickup_location')->default(0);
            $table->boolean('website')->default(0);
            $table->boolean('social')->default(0);
            $table->double('width')->default(4.0);
            $table->double('height')->default(6.0);
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
        Schema::dropIfExists('order_label_settings');
    }
}
