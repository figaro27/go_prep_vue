<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMappingIdColumnToMealPackageOrdersTable extends Migration
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
                ->string('mappingId')
                ->after('delivery_date')
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
            $table->dropColumn('mappingId');
        });
    }
}
