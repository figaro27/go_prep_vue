<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastPaymentColumnToSmsSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_settings', function (Blueprint $table) {
            $table
                ->datetime('last_payment')
                ->after('balance')
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
        Schema::table('sms_settings', function (Blueprint $table) {
            $table->dropColumn('last_payment');
        });
    }
}
