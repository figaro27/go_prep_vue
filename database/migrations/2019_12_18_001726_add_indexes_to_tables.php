<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('meals', function (Blueprint $table) {
            $table
                ->integer('store_id')
                ->unsigned()
                ->change();
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
        });

        Schema::table('meal_orders', function (Blueprint $table) {
            $table
                ->integer('store_id')
                ->unsigned()
                ->change();
            $table
                ->integer('meal_id')
                ->unsigned()
                ->change();
            $table
                ->integer('order_id')
                ->unsigned()
                ->change();

            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->foreign('meal_id')
                ->references('id')
                ->on('meals');
            $table
                ->foreign('meal_size_id')
                ->references('id')
                ->on('meal_sizes');
            $table
                ->foreign('order_id')
                ->references('id')
                ->on('orders');
        });

        Schema::table('meal_components', function (Blueprint $table) {
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->foreign('meal_id')
                ->references('id')
                ->on('meals');
        });

        Schema::table('meal_component_options', function (Blueprint $table) {
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->foreign('meal_component_id')
                ->references('id')
                ->on('meal_components');
            $table
                ->foreign('meal_size_id')
                ->references('id')
                ->on('meal_sizes');
        });

        Schema::table('meal_addons', function (Blueprint $table) {
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->foreign('meal_id')
                ->references('id')
                ->on('meals');
        });

        Schema::table('meal_order_components', function (Blueprint $table) {
            $table
                ->integer('meal_order_id')
                ->unsigned()
                ->change();
            $table
                ->integer('meal_component_id')
                ->unsigned()
                ->change();
            $table
                ->integer('meal_component_option_id')
                ->unsigned()
                ->change();

            $table
                ->foreign('meal_order_id')
                ->references('id')
                ->on('meal_orders')
                ->onDelete('cascade');
            $table
                ->foreign('meal_component_id')
                ->references('id')
                ->on('meal_components');
            $table
                ->foreign('meal_component_option_id')
                ->references('id')
                ->on('meal_component_options');
        });

        Schema::table('meal_order_addons', function (Blueprint $table) {
            $table
                ->integer('meal_order_id')
                ->unsigned()
                ->change();
            $table
                ->integer('meal_addon_id')
                ->unsigned()
                ->change();

            $table
                ->foreign('meal_order_id')
                ->references('id')
                ->on('meal_orders')
                ->onDelete('cascade');
            $table
                ->foreign('meal_addon_id')
                ->references('id')
                ->on('meal_addons');
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
        Schema::disableForeignKeyConstraints();

        Schema::table('meals', function (Blueprint $table) {
            $table->dropForeign('meals_store_id_foreign');
        });

        Schema::table('meal_orders', function (Blueprint $table) {
            $table->dropForeign('meal_orders_store_id_foreign');
            $table->dropForeign('meal_orders_meal_id_foreign');
            $table->dropForeign('meal_orders_meal_size_id_foreign');
            $table->dropForeign('meal_orders_order_id_foreign');
        });

        Schema::table('meal_components', function (Blueprint $table) {
            $table->dropForeign('meal_components_store_id_foreign');
            $table->dropForeign('meal_components_meal_id_foreign');
        });

        Schema::table('meal_component_options', function (Blueprint $table) {
            $table->dropForeign('meal_component_options_store_id_foreign');
            $table->dropForeign(
                'meal_component_options_meal_component_id_foreign'
            );
            $table->dropForeign('meal_component_options_meal_size_id_foreign');
        });

        Schema::table('meal_addons', function (Blueprint $table) {
            $table->dropForeign('meal_addons_store_id_foreign');
            $table->dropForeign('meal_addons_meal_id_foreign');
        });

        Schema::table('meal_order_components', function (Blueprint $table) {
            $table->dropForeign('meal_order_components_meal_order_id_foreign');
            $table->dropForeign(
                'meal_order_components_meal_component_id_foreign'
            );
            $table->dropForeign(
                'meal_order_components_meal_component_option_id_foreign'
            );
        });

        Schema::table('meal_order_addons', function (Blueprint $table) {
            $table->dropForeign('meal_order_addons_meal_order_id_foreign');
            $table->dropForeign('meal_order_addons_meal_addon_id_foreign');
        });

        Schema::enableForeignKeyConstraints();
    }
}
