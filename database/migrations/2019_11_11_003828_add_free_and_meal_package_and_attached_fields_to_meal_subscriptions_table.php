<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFreeAndMealPackageAndAttachedFieldsToMealSubscriptionsTable extends
    Migration
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
                ->boolean('free')
                ->after('meal_package_subscription_id')
                ->nullable();
            // Removing attached field as it's not needed.
            // $table->boolean('attached')->after('special_instructions')->nullable();
            $table
                ->boolean('meal_package')
                ->after('attached')
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
            $table->dropColumn('free');
            // $table->dropColumn('attached');
            $table->dropColumn('meal_package');
        });
    }
}
