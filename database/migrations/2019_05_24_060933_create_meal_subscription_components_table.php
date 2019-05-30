<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealSubscriptionComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_subscription_components', function (
            Blueprint $table
        ) {
            $table->increments('id');
            $table
                ->integer('meal_subscription_id')
                ->references('id')
                ->on('meal_subscriptions');
            $table
                ->integer('meal_component_id')
                ->references('id')
                ->on('meal_components');
            $table
                ->integer('meal_component_option_id')
                ->references('id')
                ->on('meal_component_options');
            $table->timestamps();

            $table->unique(
                [
                    'meal_subscription_id',
                    'meal_component_id',
                    'meal_component_option_id'
                ],
                'uniq'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meal_subscription_components');
    }
}
