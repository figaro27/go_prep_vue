<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllowMultipleSubscriptionsColumnToStoreSettingsTable extends Migration
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
                ->boolean('allowMultipleSubscriptions')
                ->after('monthlyPrepaySubscriptions')
                ->default(0);
        });

        Schema::table('store_modules', function (Blueprint $table) {
            $table->dropColumn('allowMultipleSubscriptions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
