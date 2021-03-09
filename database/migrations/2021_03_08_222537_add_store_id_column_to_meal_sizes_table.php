<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStoreIdColumnToMealSizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('meal_sizes', 'store_id')) {
            Schema::table('meal_sizes', function (Blueprint $table) {
                $table->dropColumn('store_id');
            });
        }
        Schema::disableForeignKeyConstraints();
        Schema::table('meal_sizes', function (Blueprint $table) {
            $table->unsignedInteger('store_id');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
        });
        Schema::enableForeignKeyConstraints();
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
