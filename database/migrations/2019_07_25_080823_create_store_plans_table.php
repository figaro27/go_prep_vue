<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStorePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_id')->unique();
            $table->boolean('active');
            $table->unsignedInteger('amount')->comment('In cents');
            $table->string('period');
            $table->unsignedInteger('day');
            $table->string('currency', 3)->default('usd');
            $table->date('last_charged')->nullable();
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
        Schema::dropIfExists('store_plans');
    }
}
