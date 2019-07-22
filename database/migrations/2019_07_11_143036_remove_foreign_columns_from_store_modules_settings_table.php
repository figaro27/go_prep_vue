<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveForeignColumnsFromStoreModulesSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_module_settings', function (Blueprint $table) {
            $table->dropColumn('store_settings_id');
            $table->dropColumn('store_modules_id');
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
            //
        });
    }
}
