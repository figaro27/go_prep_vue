<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealPackageAddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_package_addons', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->unsignedInteger('meal_package_id')
                ->references('id')
                ->on('meal_packages');
            $table
                ->unsignedInteger('meal_package_size_id')
                ->nullable()
                ->references('id')
                ->on('meal_package_sizes');
            $table->string('title');
            $table->double('price', 6, 2);
            $table->boolean('selectable')->default(0);
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
        Schema::dropIfExists('meal_package_addons');
    }
}
