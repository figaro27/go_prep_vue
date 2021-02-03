<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeMonthlyPrepaidSubscriptionsNameOnStoreSettingsTable extends
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
            $table->renameColumn(
                'monthlyPrepaySubscriptions',
                'prepaidSubscriptions'
            );
            $table
                ->unsignedInteger('prepaidWeeks')
                ->after('monthlyPrepaySubscriptions')
                ->default(4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
