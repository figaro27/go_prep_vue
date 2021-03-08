<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStoreIdToMealSizesTable extends Migration
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
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->unsignedInteger('store_id')
                ->after('id')
                ->references('id')
                ->on('stores');
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
            //
        });
    }
}
