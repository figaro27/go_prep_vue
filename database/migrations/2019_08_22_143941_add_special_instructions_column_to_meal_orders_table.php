<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpecialInstructionsColumnToMealOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_orders', function (Blueprint $table) {
            $table
                ->longtext('special_instructions')
                ->after('quantity')
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
        Schema::table('meal_orders', function (Blueprint $table) {
            $table->dropColumn('special_instructions');
        });
    }
}
