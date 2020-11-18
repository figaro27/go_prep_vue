<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDivideByComponentsColumnToMealPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_packages', function (Blueprint $table) {
            $table
                ->boolean('divideByComponents')
                ->after('ignoreBasePrice')
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
        Schema::table('meal_packages', function (Blueprint $table) {
            $table->dropColumn('divideByComponents');
        });
    }
}
