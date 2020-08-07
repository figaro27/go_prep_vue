<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLabPackagedByColumnToReportSettingsTable extends Migration
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
                ->boolean('lab_packaged_by')
                ->after('lab_allergies')
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
            $table->dropColumn();
        });
    }
}
