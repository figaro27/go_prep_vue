<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderNotesForCustomerOnStoreModulesSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_module_settings', function (Blueprint $table) {
            $table
                ->boolean('orderNotesForCustomer')
                ->after('updated_at')
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
        Schema::table('store_module_settings', function (Blueprint $table) {
            $table->dropColumn('orderNotesForCustomer');
        });
    }
}
