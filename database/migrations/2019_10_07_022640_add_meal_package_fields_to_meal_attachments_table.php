<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMealPackageFieldsToMealAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_attachments', function (Blueprint $table) {
            $table
                ->integer('meal_package_id')
                ->after('meal_size_id')
                ->references('id')
                ->on('meal_packages')
                ->nullable();
            $table
                ->integer('meal_package_size_id')
                ->after('meal_package_id')
                ->references('id')
                ->on('meal_package_sizes')
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
        Schema::table('meal_attachments', function (Blueprint $table) {
            $table->dropColumn('meal_package_id');
            $table->dropColumn('meal_package_size_id');
        });
    }
}
