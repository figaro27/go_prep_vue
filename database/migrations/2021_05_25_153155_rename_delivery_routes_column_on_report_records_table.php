<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameDeliveryRoutesColumnOnReportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_records', function (Blueprint $table) {
            $table->renameColumn('delivery_routes', 'delivery');
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
            $table->renameColumn('delivery', 'delivery_routes');
        });
    }
}
