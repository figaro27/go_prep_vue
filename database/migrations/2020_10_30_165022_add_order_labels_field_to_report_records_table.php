<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderLabelsFieldToReportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_records', function (Blueprint $table) {
            $table
                ->unsignedInteger('order_labels')
                ->after('labels')
                ->default(0);
            $table->renameColumn('labels', 'meal_labels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_records', function (Blueprint $table) {
            $table->dropColumn('order_labels');
            $table->renameColumn('meal_labels', 'labels');
        });
    }
}
