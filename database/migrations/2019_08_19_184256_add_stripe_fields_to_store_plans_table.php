<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStripeFieldsToStorePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_plans', function (Blueprint $table) {
            $table->string('method')->default('connect');
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_plans', function (Blueprint $table) {
            $table->dropColumn('method');
            $table->dropColumn('stripe_customer_id');
            $table->dropColumn('stripe_subscription_id');
        });
    }
}
