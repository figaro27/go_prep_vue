<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeMileageFieldTypesOnStoreSettingsTable extends Migration
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
                ->decimal('mileageBase')
                ->nullable()
                ->change();
            $table
                ->decimal('mileagePerMile')
                ->nullable()
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
        $table->dropColumn('mileageBase');
        $table->dropColumn('mileagePerMile');
    }
}
