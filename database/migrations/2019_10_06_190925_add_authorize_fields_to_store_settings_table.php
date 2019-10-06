<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAuthorizeFieldsToStoreSettingsTable extends Migration
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
                ->string('authorize_login_id')
                ->nullable()
                ->after('payment_gateway');
            $table
                ->string('authorize_transaction_key')
                ->nullable()
                ->after('authorize_login_id');
            $table
                ->string('authorize_public_key')
                ->nullable()
                ->after('authorize_transaction_key');
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
            $table->dropColumn('authorize_login_id');
            $table->dropColumn('authorize_transaction_key');
            $table->dropColumn('authorize_public_key');
        });
    }
}
