<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributesAsFieldsToOrdersTable extends Migration
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
                ->string('staff_member')
                ->after('staff_id')
                ->nullable();
            $table
                ->string('pickup_location_name')
                ->after('pickup_location_id')
                ->nullable();
            $table
                ->string('purchased_gift_card_code')
                ->after('purchased_gift_card_id')
                ->nullable();
            $table
                ->string('store_name')
                ->after('store_id')
                ->nullable();
            $table
                ->string('transfer_type')
                ->after('order_number')
                ->nullable();
            $table
                ->string('customer_name')
                ->after('customer_id')
                ->nullable();
            $table
                ->string('customer_address')
                ->after('delivery_date')
                ->nullable();
            $table
                ->string('customer_zip')
                ->after('customer_address')
                ->nullable();
            $table
                ->decimal('goprep_fee')
                ->after('amount')
                ->default(0);
            $table
                ->decimal('stripe_fee')
                ->after('goprep_fee')
                ->default(0);
            $table
                ->decimal('grandTotal')
                ->after('stripe_fee')
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
            $table->dropColumn('staff_member');
        });
    }
}
