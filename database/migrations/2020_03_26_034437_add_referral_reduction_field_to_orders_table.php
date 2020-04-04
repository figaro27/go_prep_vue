<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReferralReductionFieldToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table
                ->unsignedInteger('applied_referral_id')
                ->after('couponCode')
                ->references('id')
                ->on('referrals')
                ->nullable();
            $table
                ->decimal('referralReduction')
                ->after('applied_referral_id')
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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('applied_referral_id');
            $table->dropColumn('referralReduction');
        });
    }
}
