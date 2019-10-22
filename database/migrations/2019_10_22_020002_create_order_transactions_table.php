<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->unsignedInteger('order_id')
                ->references('id')
                ->on('orders');
            $table
                ->unsignedInteger('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->unsignedInteger('user_id')
                ->references('id')
                ->on('users');
            $table
                ->unsignedInteger('customer_id')
                ->references('id')
                ->on('customers');
            $table
                ->unsignedInteger('card_id')
                ->references('id')
                ->on('cards');
            $table->string('type');
            $table->string('stripe_id');
            $table->decimal('amount');
            $table->boolean('applyToBalance')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_transactions');
    }
}
