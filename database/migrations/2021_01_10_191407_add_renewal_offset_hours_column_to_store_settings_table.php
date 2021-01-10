<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRenewalOffsetHoursColumnToStoreSettingsTable extends Migration
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
                ->unsignedInteger('renewalOffsetHours')
                ->after('subscriptionRenewalType')
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
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('renewalOffsetHours');
        });
    }
}
