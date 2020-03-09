<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLabelMarginFieldsToReportSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_settings', function (Blueprint $table) {
            $table->float('lab_margin_top')->default(0.0);
            $table->float('lab_margin_right')->default(0.0);
            $table->float('lab_margin_bottom')->default(0.0);
            $table->float('lab_margin_left')->default(0.0);
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
            $table->dropColumn('lab_margin_top');
            $table->dropColumn('lab_margin_right');
            $table->dropColumn('lab_margin_bottom');
            $table->dropColumn('lab_margin_left');
        });
    }
}
