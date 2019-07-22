<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRestrictMealsOptionIdFieldToMealPackageComponentOptionsTable extends
    Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_package_component_options', function (
            Blueprint $table
        ) {
            $table->unsignedInteger('restrict_meals_option_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meal_package_component_options', function (
            Blueprint $table
        ) {
            $table->dropColumn('restrict_meals_option_id');
        });
    }
}
