<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddedPriceFieldsToMealSubscriptionsTable extends Migration
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
                ->decimal('added_price')
                ->after('price')
                ->default(0);
        });
        Schema::table('meal_orders', function (Blueprint $table) {
            $table
                ->decimal('added_price')
                ->after('price')
                ->default(0);
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
            $table->dropColumn('added_price');
        });
        Schema::table('meal_orders', function (Blueprint $table) {
            $table->dropColumn('added_price');
        });
    }
}
