<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMealPackageSubscriptionIdToMealSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_subscriptions', function (Blueprint $table) {
            $table
                ->integer('meal_package_subscription_id')
                ->references('id')
                ->on('meal_package_subscriptions')
                ->after('special_instructions')
                ->nullable();
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
            $table->dropColumn('meal_package_subscription_id');
        });
    }
}
