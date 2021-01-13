<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddItemsQuantityColumnToMealPackageOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_package_orders', function (Blueprint $table) {
            $table
                ->unsignedInteger('items_quantity')
                ->after('category_id')
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
        Schema::table('meal_package_orders', function (Blueprint $table) {
            $table->dropColumn('items_quantity');
        });
    }
}
