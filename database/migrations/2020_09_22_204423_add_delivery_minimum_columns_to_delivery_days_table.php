<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryMinimumColumnsToDeliveryDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_days', function (Blueprint $table) {
            $table
                ->decimal('minimum')
                ->after('cutoff_hours')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_days', function (Blueprint $table) {
            $table->dropColumn('minimum');
        });
    }
}
