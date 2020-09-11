<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_records', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_id');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table->unsignedInteger('orders')->default(0);
            $table->unsignedInteger('order_summaries')->default(0);
            $table->unsignedInteger('packing_slips')->default(0);
            $table->unsignedInteger('meal_production')->default(0);
            $table->unsignedInteger('labels')->default(0);
            $table->unsignedInteger('delivery_routes')->default(0);
            $table->unsignedInteger('payments')->default(0);
            $table->unsignedInteger('customers')->default(0);
            $table->unsignedInteger('subscriptions')->default(0);
            $table->unsignedInteger('referrals')->default(0);
            $table->unsignedInteger('leads')->default(0);
            $table->unsignedInteger('meals')->default(0);
            $table->unsignedInteger('ingredients_production')->default(0);
            $table->unsignedInteger('meal_ingredients')->default(0);
            $table->unsignedInteger('ingredients_by_meal')->default(0);
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
        Schema::dropIfExists('report_records');
    }
}
