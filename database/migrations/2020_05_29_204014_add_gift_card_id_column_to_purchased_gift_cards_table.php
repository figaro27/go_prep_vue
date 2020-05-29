<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGiftCardIdColumnToPurchasedGiftCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchased_gift_cards', function (Blueprint $table) {
            $table
                ->unsignedInteger('gift_card_id')
                ->after('store_id')
                ->nullable();
            $table
                ->foreign('gift_card_id')
                ->references('id')
                ->on('gift_cards');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchased_gift_cards', function (Blueprint $table) {
            //
        });
    }
}
