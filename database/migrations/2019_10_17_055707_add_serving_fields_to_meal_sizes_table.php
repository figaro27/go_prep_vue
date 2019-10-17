<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddServingFieldsToMealSizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_sizes', function (Blueprint $table) {
            $table
                ->unsignedInteger('servingsPerMeal')
                ->after('price')
                ->default(1);
            $table
                ->string('servingSizeUnit')
                ->after('servingsPerMeal')
                ->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meal_sizes', function (Blueprint $table) {
            $table->dropColumn('servingsPerMeal');
            $table->dropColumn('servingSizeUnit');
        });
    }
}
