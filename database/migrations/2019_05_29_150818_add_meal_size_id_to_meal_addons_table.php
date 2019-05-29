<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMealSizeIdToMealAddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_addons', function (Blueprint $table) {
            $table
                ->unsignedInteger('meal_size_id')
                ->nullable()
                ->after('meal_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meal_addons', function (Blueprint $table) {
            $table->dropColumn('meal_size_id');
        });
    }
}
