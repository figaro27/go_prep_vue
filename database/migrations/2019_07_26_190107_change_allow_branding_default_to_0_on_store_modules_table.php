<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAllowBrandingDefaultTo0OnStoreModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_modules', function (Blueprint $table) {
            $table
                ->boolean('hideBranding')
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
        Schema::table('store_modules', function (Blueprint $table) {
            //
        });
    }
}
