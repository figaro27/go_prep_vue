<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoSendSubscriptionRenewalFieldToSmsSettingsTable extends Migration
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
                ->boolean('autoSendSubscriptionRenewal')
                ->after('autoSendOrderConfirmationTemplate')
                ->default(0);
            $table
                ->text('autoSendSubscriptionRenewalTemplate')
                ->after('autoSendSubscriptionRenewal');
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
            $table->dropColumn('autoSendSubscriptionRenewal');
            $table->dropColumn('autoSendSubscriptionRenewalTemplate');
        });
    }
}
