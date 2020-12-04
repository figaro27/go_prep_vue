<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllowMonthlySubscriptionsFieldToStoreSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->renameColumn('allowMealPlans', 'allowWeeklySubscriptions');
            $table
                ->boolean('allowMonthlySubscriptions')
                ->after('allowMealPlans')
                ->default(0);
            $table
                ->boolean('monthlyPrepaySubscriptions')
                ->after('allowMonthlySubscriptions')
                ->default(0);
        });

        Schema::table('store_modules', function (Blueprint $table) {
            $table->dropColumn('monthlyPlans');
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
            //
        });
    }
}
