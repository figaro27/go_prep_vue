<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealPackageSelectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_package_selections', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->unsignedInteger('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->unsignedInteger('meal_package_id')
                ->references('id')
                ->on('meal_packages');
            $table
                ->unsignedInteger('meal_package_size_id')
                ->references('id')
                ->on('meal_package_sizes');
            $table->string('title');
            $table->unsignedInteger('minimum');
            $table->unsignedInteger('maximum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meal_package_selections');
    }
}
