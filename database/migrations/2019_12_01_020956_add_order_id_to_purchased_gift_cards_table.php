<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderIdToPurchasedGiftCardsTable extends Migration
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
                ->integer('order_id')
                ->references('id')
                ->on('orders')
                ->after('user_id');
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
            $table->dropColumn('order_id');
        });
    }
}
