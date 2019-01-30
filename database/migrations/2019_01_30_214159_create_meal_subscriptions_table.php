<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id')->references('id')->on('stores');
            $table->integer('subscription_id')->references('id')->on('subscriptions');
            $table->integer('meal_id')->references('id')->on('meals');
            $table->integer('quantity')->default(1);
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
        Schema::dropIfExists('meal_subscriptions');
    }
}
