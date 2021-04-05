<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TransferTotalPaymentsFieldsToUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('total_payments');
            $table->dropColumn('multiple_store_orders');
        });

        Schema::table('user_details', function (Blueprint $table) {
            $table->unsignedInteger('last_viewed_store_id')->nullable();
            $table->boolean('multiple_store_orders')->default(0);
            $table->unsignedInteger('total_payments')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_details', function (Blueprint $table) {
            //
        });
    }
}
