<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMealSizeFieldToMealMealPackageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_meal_package', function (Blueprint $table) {
            $table
                ->unsignedInteger('meal_size_id')
                ->nullable()
                ->after('meal_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meal_meal_package', function (Blueprint $table) {
            $table->dropColumn('meal_size_id');
        });
    }
}
