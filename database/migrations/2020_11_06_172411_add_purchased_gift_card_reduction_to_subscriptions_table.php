<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPurchasedGiftCardReductionToSubscriptionsTable extends Migration
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
                ->unsignedInteger('purchased_gift_card_id')
                ->references('id')
                ->on('purchased_gift_cards')
                ->after('referralReduction')
                ->nullable();
            $table
                ->decimal('purchasedGiftCardReduction')
                ->after('purchased_gift_card_id')
                ->default(0);
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
            $table->dropColumn('purchased_gift_card_id');
            $table->dropColumn('purchasedGiftCardReduction');
        });
    }
}
