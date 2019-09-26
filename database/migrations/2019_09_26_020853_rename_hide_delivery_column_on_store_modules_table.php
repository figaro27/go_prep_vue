<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameHideDeliveryColumnOnStoreModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_modules', function (Blueprint $table) {
            $table->renameColumn('hideDelivery', 'hideTransferOptions');
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
