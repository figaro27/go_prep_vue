<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSizeIdFieldToMealOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_orders', function (Blueprint $table) {
            $table
                ->unsignedInteger('meal_size_id')
                ->nullable()
                ->after('meal_id');
            $table->dropUnique(['order_id', 'meal_id']);
            $table->unique(['order_id', 'meal_id', 'meal_size_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meal_orders', function (Blueprint $table) {
            $table->dropColumn('meal_size_id');
            $table->dropUnique(['order_id', 'meal_id', 'meal_size_id']);
            $table->unique(['order_id', 'meal_id']);
        });
    }
}
