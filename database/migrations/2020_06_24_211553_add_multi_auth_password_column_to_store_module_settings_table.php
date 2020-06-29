<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMultiAuthPasswordColumnToStoreModuleSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_module_settings', function (Blueprint $table) {
            $table->string('multiAuthPassword')->nullable();
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
            $table->dropColumn('multiAuthPassword');
        });
    }
}