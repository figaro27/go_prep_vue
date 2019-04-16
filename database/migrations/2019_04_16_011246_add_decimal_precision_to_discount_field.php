<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDecimalPrecisionToDiscountField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('mealPlanDiscount')->change();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('mealPlanDiscount')->change();
        });

        Schema::table('store_settings', function (Blueprint $table) {
            $table->decimal('mealPlanDiscount')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
