<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->references('id')->on('users');
            $table->integer('customer_id')->references('id')->on('customers');
            $table->integer('store_id')->references('id')->on('stores');
            $table->integer('subscription_id')->references('id')->on('subscriptions')->nullable();
            $table->string('order_number')->unique();
            $table->integer('amount');
            $table->boolean('pickup')->default(0);
            $table->date('delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->integer('processingFee')->nullable();
            $table->integer('deliveryFee')->nullable();
            $table->integer('mealPlanDiscount')->nullable();
            $table->boolean('fulfilled');
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
        Schema::dropIfExists('orders');
    }
}
