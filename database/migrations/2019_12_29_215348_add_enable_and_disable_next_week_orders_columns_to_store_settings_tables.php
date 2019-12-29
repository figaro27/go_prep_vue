<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnableAndDisableNextWeekOrdersColumnsToStoreSettingsTables extends
    Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table
                ->string('disableNextWeekDay')
                ->after('deliveryWeeks')
                ->nullable();
            $table
                ->unsignedInteger('disableNextWeekHour')
                ->after('disableNextWeekDay')
                ->nullable();
            $table
                ->string('enableNextWeekDay')
                ->after('disableNextWeekHour')
                ->nullable();
            $table
                ->unsignedInteger('enableNextWeekHour')
                ->after('enableNextWeekDay')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('disableNextWeekDay');
            $table->dropColumn('disableNextWeekHour');
            $table->dropColumn('enableNextWeekDay');
            $table->dropColumn('enableNextWeekHour');
        });
    }
}
