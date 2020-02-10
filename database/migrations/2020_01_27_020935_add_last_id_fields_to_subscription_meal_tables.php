<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastIdFieldsToSubscriptionMealTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('last_meal_id')->nullable();
            $table->unsignedInteger('last_meal_size_id')->nullable();
        });

        Schema::table('meal_subscription_components', function (
            Blueprint $table
        ) {
            $table->unsignedInteger('last_meal_component_id')->nullable();
            $table
                ->unsignedInteger('last_meal_component_option_id')
                ->nullable();
        });

        Schema::table('meal_subscription_addons', function (Blueprint $table) {
            $table->unsignedInteger('last_meal_addon_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meal_subscriptions', function (Blueprint $table) {
            $table->dropColumn('last_meal_id');
            $table->dropColumn('last_meal_size_id');
        });

        Schema::table('meal_subscription_components', function (
            Blueprint $table
        ) {
            $table->dropColumn('last_meal_component_id');
            $table->dropColumn('last_meal_component_option_id');
        });

        Schema::table('meal_subscription_addons', function (Blueprint $table) {
            $table->dropColumn('last_meal_addon_id');
        });
    }
}
