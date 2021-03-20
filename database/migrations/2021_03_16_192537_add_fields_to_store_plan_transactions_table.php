<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToStorePlanTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('store_plan_transactions', function (Blueprint $table) {
            $table
                ->unsignedInteger('store_id')
                ->after('id')
                ->references('id')
                ->on('stores');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->string('card_brand')
                ->after('currency')
                ->nullable();
            $table
                ->string('card_expiration')
                ->after('card_brand')
                ->nullable();
            $table
                ->string('card_last4')
                ->after('card_expiration')
                ->nullable();
            $table
                ->dateTime('period_start')
                ->after('card_last4')
                ->nullable();
            $table
                ->dateTime('period_end')
                ->after('period_start')
                ->nullable();
            $table
                ->string('receipt_url')
                ->after('card_last4')
                ->nullable();
            $table->dropColumn('timestamp');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_plan_transactions', function (Blueprint $table) {
            //
        });
    }
}
