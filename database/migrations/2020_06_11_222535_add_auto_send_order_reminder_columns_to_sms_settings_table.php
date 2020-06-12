<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoSendOrderReminderColumnsToSmsSettingsTable extends Migration
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
                ->boolean('autoSendOrderReminder')
                ->after('autoAddCustomers')
                ->default(0);
            $table
                ->unsignedInteger('autoSendOrderReminderHours')
                ->after('autoSendOrderReminder')
                ->default(6);
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
            $table->dropColumn('autoSendOrderReminder');
            $table->dropColumn('autoSendOrderReminderHours');
        });
    }
}
