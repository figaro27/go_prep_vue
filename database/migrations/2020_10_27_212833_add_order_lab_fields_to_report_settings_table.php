<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderLabFieldsToReportSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_settings', function (Blueprint $table) {
            $table->boolean('o_lab_customer')->default(1);
            $table->boolean('o_lab_address')->default(1);
            $table->boolean('o_lab_phone')->default(1);
            $table->boolean('o_lab_delivery')->default(1);
            $table->boolean('o_lab_order_number')->default(1);
            $table->boolean('o_lab_order_date')->default(1);
            $table->boolean('o_lab_delivery_date')->default(1);
            $table->boolean('o_lab_amount')->default(1);
            $table->boolean('o_lab_balance')->default(1);
            $table->boolean('o_lab_daily_order_number')->default(0);
            $table->boolean('o_lab_pickup_location')->default(0);
            $table->float('o_lab_width')->default(4.0);
            $table->float('o_lab_height')->default(6.0);
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
            $table->dropColumn('o_lab_customer');
            $table->dropColumn('o_lab_address');
            $table->dropColumn('o_lab_phone');
            $table->dropColumn('o_lab_delivery');
            $table->dropColumn('o_lab_order_number');
            $table->dropColumn('o_lab_daily_order_number');
            $table->dropColumn('o_lab_pickup_location');
            $table->dropColumn('o_lab_order_date');
            $table->dropColumn('o_lab_delivery_date');
            $table->dropColumn('o_lab_amount');
            $table->dropColumn('o_lab_balance');
            $table->dropColumn('o_lab_width');
            $table->dropColumn('o_lab_width');
        });
    }
}
