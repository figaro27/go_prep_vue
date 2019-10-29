<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMealPackageOrderIdFieldToMealOrdersTable extends Migration
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
                ->integer('meal_package_order_id')
                ->references('id')
                ->on('meal_package_orders')
                ->after('meal_package_title')
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
        Schema::table('meal_orders', function (Blueprint $table) {
            $table->dropColumn('meal_package_order_id');
        });
    }
}
