<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMealPackageVariationToMealOrdersTable extends Migration
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
                ->boolean('meal_package_variation')
                ->after('meal_package_title')
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
        Schema::table('meal_orders', function (Blueprint $table) {
            $table->dropColumn('meal_package_variation');
        });
    }
}
