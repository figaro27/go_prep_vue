<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNutritionixCallsFieldToReportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_records', function (Blueprint $table) {
            $table
                ->unsignedInteger('daily_nutritionix_calls')
                ->after('ingredients_by_meal')
                ->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_records', function (Blueprint $table) {
            //
        });
    }
}
