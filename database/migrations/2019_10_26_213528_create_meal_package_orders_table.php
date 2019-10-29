<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealPackageOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_package_orders', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->integer('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->integer('order_id')
                ->references('id')
                ->on('orders');
            $table
                ->integer('meal_package_id')
                ->references('id')
                ->on('meal_packages');
            $table
                ->integer('meal_package_size_id')
                ->references('id')
                ->on('meal_package_sizes')
                ->nullable();
            $table->integer('quantity')->default(1);
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
        Schema::dropIfExists('meal_package_orders');
    }
}
