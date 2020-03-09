<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLabelSizeFieldsToReportSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_settings', function (Blueprint $table) {
            $table->dropColumn('lab_size');
            $table->float('lab_width')->default(4.0);
            $table->float('lab_height')->default(6.0);
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
            $table->dropColumn('lab_width');
            $table->dropColumn('lab_height');
            $table->string('lab_size')->default('4" x 6"');
        });
    }
}
