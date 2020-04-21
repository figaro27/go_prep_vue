<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIngredientAndAllergyFieldsToReportSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_settings', function (Blueprint $table) {
            $table
                ->boolean('lab_ingredients')
                ->after('lab_expiration')
                ->default(0);
            $table
                ->boolean('lab_allergies')
                ->after('lab_ingredients')
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
        Schema::table('report_settings', function (Blueprint $table) {
            //
        });
    }
}
