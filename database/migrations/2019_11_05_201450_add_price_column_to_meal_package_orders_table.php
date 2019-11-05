<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceColumnToMealPackageOrdersTable extends Migration
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
                ->decimal('price')
                ->after('quantity')
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
        Schema::table('meal_package_orders', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
}
