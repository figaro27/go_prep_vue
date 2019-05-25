<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealSubscriptionAddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_subscription_addons', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->integer('meal_subscription_id')
                ->references('id')
                ->on('meal_subscriptions');
            $table
                ->integer('meal_addon_id')
                ->references('id')
                ->on('meal_addons');
            $table->timestamps();

            $table->unique(['meal_subscription_id', 'meal_addon_id'], 'uniq');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meal_subscription_addons');
    }
}
