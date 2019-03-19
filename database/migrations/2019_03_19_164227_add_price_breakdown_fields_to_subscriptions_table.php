<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceBreakdownFieldsToSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->double('preFeePreDiscount')->nullable();
            $table->double('mealPlanDiscount')->nullable();
            $table->double('afterDiscountBeforeFees')->nullable();
            $table->double('processingFee')->nullable();
            $table->double('deliveryFee')->nullable();
            $table->double('salesTax')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('preFeePreDiscount');
            $table->dropColumn('mealPlanDiscount');
            $table->dropColumn('afterDiscountBeforeFees');
            $table->dropColumn('processingFee');
            $table->dropColumn('deliveryFee');
            $table->dropColumn('salesTax');
        });
    }
}


