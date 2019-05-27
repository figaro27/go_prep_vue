<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeShowLogoFieldDefaultTo0OnStoreSettingsTable extends Migration
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
                ->boolean('showLogo')
                ->default(0)
                ->change();
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
            $table->dropColumn('showLogo');
        });
    }
}
