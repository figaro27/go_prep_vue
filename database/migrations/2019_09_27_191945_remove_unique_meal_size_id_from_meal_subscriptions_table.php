<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUniqueMealSizeIdFromMealSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_subscriptions', function (Blueprint $table) {
            $table->dropUnique(['subscription_id', 'meal_id', 'meal_size_id']);
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
            //
        });
    }
}
