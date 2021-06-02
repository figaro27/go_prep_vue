<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerSurveyDaysOffsetColumnToStoreModuleSettingTable extends
    Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_module_settings', function (Blueprint $table) {
            $table->unsignedInteger('customerSurveyDays')->default(3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_module_settings', function (Blueprint $table) {
            $table->dropColumn('customerSurveyDays');
        });
    }
}
