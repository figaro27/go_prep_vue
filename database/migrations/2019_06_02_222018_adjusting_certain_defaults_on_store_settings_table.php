<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdjustingCertainDefaultsOnStoreSettingsTable extends Migration
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
                ->string('closedReason')
                ->default('Setting up!')
                ->change();
            $table
                ->decimal('minimumPrice')
                ->default(20.0)
                ->nullable()
                ->change();
            $table
                ->decimal('deliveryFee')
                ->default(1.0)
                ->nullable()
                ->change();
            $table
                ->boolean('meal_packages')
                ->default(1)
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
        //
    }
}
