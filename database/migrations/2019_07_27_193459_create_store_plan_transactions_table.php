<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStorePlanTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_plan_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_plan_id');
            $table->string('stripe_id');
            $table->unsignedInteger('amount')->comment('In cents');
            $table->string('currency');
            $table->timestamp('timestamp');
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
        Schema::dropIfExists('store_plan_transactions');
    }
}
