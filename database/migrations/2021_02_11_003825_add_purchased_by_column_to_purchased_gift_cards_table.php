<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPurchasedByColumnToPurchasedGiftCardsTable extends Migration
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
                ->string('purchased_by')
                ->after('emailRecipient')
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
        Schema::table('purchased_gift_cards', function (Blueprint $table) {
            //
        });
    }
}
