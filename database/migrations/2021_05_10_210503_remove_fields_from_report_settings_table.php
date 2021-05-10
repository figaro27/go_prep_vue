<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveFieldsFromReportSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_settings', function (Blueprint $table) {
            $table->dropColumn('lab_index');
            $table->dropColumn('lab_nutrition');
            $table->dropColumn('lab_macros');
            $table->dropColumn('lab_logo');
            $table->dropColumn('lab_website');
            $table->dropColumn('lab_social');
            $table->dropColumn('lab_customer');
            $table->dropColumn('lab_description');
            $table->dropColumn('lab_instructions');
            $table->dropColumn('lab_expiration');
            $table->dropColumn('lab_ingredients');
            $table->dropColumn('lab_allergies');
            $table->dropColumn('lab_packaged_by');
            $table->dropColumn('lab_packaged_on');
            $table->dropColumn('lab_dailyOrderNumbers');
            $table->dropColumn('lab_width');
            $table->dropColumn('lab_height');
            $table->dropColumn('lab_margin_top');
            $table->dropColumn('lab_margin_bottom');
            $table->dropColumn('lab_margin_left');
            $table->dropColumn('lab_margin_right');
            $table->dropColumn('o_lab_customer');
            $table->dropColumn('o_lab_address');
            $table->dropColumn('o_lab_phone');
            $table->dropColumn('o_lab_delivery');
            $table->dropColumn('o_lab_order_number');
            $table->dropColumn('o_lab_order_date');
            $table->dropColumn('o_lab_delivery_date');
            $table->dropColumn('o_lab_amount');
            $table->dropColumn('o_lab_balance');
            $table->dropColumn('o_lab_daily_order_number');
            $table->dropColumn('o_lab_pickup_location');
            $table->dropColumn('o_lab_website');
            $table->dropColumn('o_lab_social');
            $table->dropColumn('o_lab_width');
            $table->dropColumn('o_lab_height');

            $table
                ->boolean('noQuantityMergeOnPackageReport')
                ->after('store_id')
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
