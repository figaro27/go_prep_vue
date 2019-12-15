<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSalesTaxColumnOnMealSizesTable extends Migration
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
                ->decimal('salesTax')
                ->after('multiplier')
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
        Schema::table('meal_sizes', function (Blueprint $table) {
            $table->dropColumn('salesTax');
        });
    }
}
