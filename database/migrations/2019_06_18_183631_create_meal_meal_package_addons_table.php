<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealMealPackageAddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_meal_package_addons', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->unsignedInteger('meal_package_addon_id')
                ->references('id')
                ->on('meal_package_addons');
            $table
                ->unsignedInteger('meal_id')
                ->references('id')
                ->on('meals');
            $table
                ->unsignedInteger('meal_size_id')
                ->references('id')
                ->on('meal_sizes')
                ->nullable();
            $table->unsignedInteger('quantity');
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
        Schema::dropIfExists('meal_meal_package_addons');
    }
}
