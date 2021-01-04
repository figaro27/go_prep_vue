<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimestampFieldsToSubscriptionsTable extends Migration
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
                ->unsignedInteger('paid_order_count')
                ->after('renewalCount')
                ->default(0);
            $table
                ->timestamp('latest_unpaid_order_date')
                ->after('paused_at')
                ->nullable();
            $table
                ->timestamp('next_delivery_date')
                ->after('delivery_day')
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
            $table->dropColumn('paid_order_count');
            $table->dropColumn('latest_unpaid_order_date');
            $table->dropColumn('next_delivery_date');
        });
    }
}
