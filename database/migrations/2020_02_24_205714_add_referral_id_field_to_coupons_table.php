<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReferralIdFieldToCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table
                ->unsignedInteger('referral_user_id')
                ->after('oneTime')
                ->nullable();
            $table
                ->foreign('referral_user_id')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('referral_user_id');
        });
    }
}
