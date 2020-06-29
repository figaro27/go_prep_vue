<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryDateColumnToMealPackageSubscriptionsTable extends Migration
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
                ->date('delivery_date')
                ->after('price')
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
            $table->dropColumn('delivery_date');
        });
    }
}
