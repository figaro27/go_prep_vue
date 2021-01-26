<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_id');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->unsignedInteger('user_id')
                ->references('id')
                ->on('users');
            $table->string('customer')->nullable();
            $table
                ->unsignedInteger('order_id')
                ->references('id')
                ->on('orders')
                ->nullable();
            $table->string('order_number')->nullable();
            $table
                ->unsignedInteger('card_id')
                ->references('id')
                ->on('cards')
                ->nullable();
            $table->string('stripe_id')->nullable();
            $table->string('charge_id')->nullable();
            $table->decimal('amount')->nullable();
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
        Schema::dropIfExists('refunds');
    }
}
