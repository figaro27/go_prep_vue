<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomTitleAndCustomSizeFieldsToMealPackageSubscriptionsTable extends
    Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_package_subscriptions', function (
            Blueprint $table
        ) {
            $table
                ->string('customTitle')
                ->after('price')
                ->nullable();
            $table
                ->string('customSize')
                ->after('customTitle')
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
        Schema::table('meal_package_subscriptions', function (
            Blueprint $table
        ) {
            $table->dropColumn('customTitle');
            $table->dropColumn('customSize');
        });
    }
}
