<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveColumnsToDeliveryDaysTable extends Migration
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
                ->boolean('active')
                ->after('store_id')
                ->default(1);
            $table
                ->string('disableDay')
                ->after('mileagePerMile')
                ->nullable();
            $table
                ->unsignedInteger('disableHour')
                ->after('disableDay')
                ->nullable();
            $table
                ->string('enableDay')
                ->after('disableHour')
                ->nullable();
            $table
                ->unsignedInteger('enableHour')
                ->after('enableDay')
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
            $table->dropColumn('active');
            $table->dropColumn('disableDay');
            $table->dropColumn('disableHour');
            $table->dropColumn('enableDay');
            $table->dropColumn('enableHour');
        });
    }
}
