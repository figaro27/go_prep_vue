<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInternationalFieldsToStoreTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_details', function (Blueprint $table) {
            $table
                ->string('country', 2)
                ->default('US')
                ->after('zip');
        });

        Schema::table('store_settings', function (Blueprint $table) {
            $table
                ->string('currency', 3)
                ->default('USD')
                ->after('timezone');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table
                ->string('currency', 3)
                ->default('USD')
                ->after('amount');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table
                ->string('currency', 3)
                ->default('USD')
                ->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_details', function (Blueprint $table) {
            $table->dropColumn('country');
        });

        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
}
