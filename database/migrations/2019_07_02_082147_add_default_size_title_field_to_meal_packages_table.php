<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultSizeTitleFieldToMealPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_packages', function (Blueprint $table) {
            $table
                ->string('default_size_title')
                ->nullable()
                ->after('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meal_packages', function (Blueprint $table) {
            $table->dropColumn('default_size_title');
        });
    }
}
