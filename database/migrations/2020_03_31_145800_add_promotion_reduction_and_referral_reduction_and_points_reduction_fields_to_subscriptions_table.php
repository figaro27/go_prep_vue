<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPromotionReductionAndReferralReductionAndPointsReductionFieldsToSubscriptionsTable
    extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table
                ->unsignedInteger('applied_referral_id')
                ->references('id')
                ->on('referrals')
                ->after('couponCode')
                ->nullable();
            $table
                ->foreign('applied_referral_id')
                ->references('id')
                ->on('referrals')
                ->after('couponCode')
                ->nullable();
            $table
                ->decimal('referralReduction')
                ->after('applied_referral_id')
                ->nullable();
            $table
                ->decimal('promotionReduction')
                ->after('referralReduction')
                ->nullable();
            $table
                ->decimal('pointsReduction')
                ->after('promotionReduction')
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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('applied_referral_id');
            $table->dropColumn('referralReduction');
            $table->dropColumn('promotionReduction');
            $table->dropColumn('pointsReduction');
        });
    }
}
