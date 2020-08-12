<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCoolerBagColumnToStoreModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_modules', function (Blueprint $table) {
            $table->boolean('cooler')->default(0);
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
            $table->dropColumn('cooler');
        });
    }
}