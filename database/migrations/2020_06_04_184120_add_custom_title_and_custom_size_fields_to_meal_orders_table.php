<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomTitleAndCustomSizeFieldsToMealOrdersTable extends Migration
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
                ->string('customTitle')
                ->after('hidden')
                ->nullable();
            $table
                ->string('customSize')
                ->after('customTitle')
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
            $table->dropColumn('customTitle');
            $table->dropColumn('customSize');
        });
    }
}
