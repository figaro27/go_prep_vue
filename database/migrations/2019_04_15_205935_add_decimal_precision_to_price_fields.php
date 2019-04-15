<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDecimalPrecisionToPriceFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('preFeePreDiscount')->change();
            $table->decimal('afterDiscountBeforeFees')->change();
            $table->decimal('processingFee')->change();
            $table->decimal('deliveryFee')->change();
            $table->decimal('amount')->change();
            $table->decimal('salesTax')->change();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            // Change status column to varchar or DBAL will fail
            // MySQL enum field sucks
            DB::statement(
                "ALTER TABLE `subscriptions` CHANGE `status` `status` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active';"
            );

            $table->decimal('preFeePreDiscount')->change();
            $table->decimal('afterDiscountBeforeFees')->change();
            $table->decimal('processingFee')->change();
            $table->decimal('deliveryFee')->change();
            $table->decimal('amount')->change();
            $table->decimal('salesTax')->change();
        });

        Schema::table('store_settings', function (Blueprint $table) {
            // Change status column to varchar or DBAL will fail
            // MySQL enum field sucks
            DB::statement(
                "ALTER TABLE `store_settings` CHANGE `delivery_distance_type` `delivery_distance_type` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'radius';"
            );

            $table->decimal('deliveryFee')->change();
            $table->decimal('processingFee')->change();
            $table->decimal('application_fee')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // DBAL can't change to doubles.
    }
}
