<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeTitleFieldNullableInMealPackageComponentsOptionsTable extends
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
            $table
                ->string('title')
                ->nullable()
                ->change();
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
            $table
                ->string('title')
                ->nullable(false)
                ->change();
        });
    }
}
