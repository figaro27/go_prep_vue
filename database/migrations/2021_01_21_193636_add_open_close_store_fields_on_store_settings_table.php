<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOpenCloseStoreFieldsOnStoreSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('closeStoreDay')->nullable();
            $table->unsignedInteger('closeStoreHour')->nullable();
            $table->string('openStoreDay')->nullable();
            $table->unsignedInteger('openStoreHour')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->dropColumn('closeStoreDay');
        $table->dropColumn('closeStoreHour');
        $table->dropColumn('openStoreDay');
        $table->dropColumn('openStoreHour');
    }
}
