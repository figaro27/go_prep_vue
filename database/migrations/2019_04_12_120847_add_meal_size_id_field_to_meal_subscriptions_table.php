<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMealSizeIdFieldToMealSubscriptionsTable extends Migration
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
                ->unsignedInteger('meal_size_id')
                ->nullable()
                ->after('meal_id');
            $table->dropUnique(['subscription_id', 'meal_id']);
            $table->unique(['subscription_id', 'meal_id', 'meal_size_id']);
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
            $table->dropColumn('meal_size_id');
            $table->dropUnique(['subscription_id', 'meal_id', 'meal_size_id']);
            $table->unique(['subscription_id', 'meal_id']);
        });
    }
}
