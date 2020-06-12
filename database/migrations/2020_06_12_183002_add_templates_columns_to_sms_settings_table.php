<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTemplatesColumnsToSmsSettingsTable extends Migration
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
                ->string('autoSendOrderReminderTemplate')
                ->after('autoSendOrderReminderHours')
                ->default(
                    'Last chance to order for {next delivery}. Our cutoff time is {cutoff}. Please order at {URL}.'
                );
            $table
                ->string('autoSendDeliveryTemplate')
                ->after('autoSendDeliveryTime')
                ->default(
                    'Your order from {store name} {pickup/delivery} today.'
                );
            $table
                ->string('autoSendOrderConfirmationTemplate')
                ->after('autoSendOrderConfirmation')
                ->default(
                    'Thank you for your order. Your order {pickup/delivery} on {delivery date}.'
                );
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
            $table->dropColumn('autoSendOrderReminderTemplate');
            $table->dropColumn('autoSendDeliveryTemplate');
            $table->dropColumn('autoSendOrderConfirmationTemplate');
        });
    }
}
