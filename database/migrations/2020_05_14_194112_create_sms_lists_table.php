<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_id');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table->unsignedInteger('list_id');
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
        Schema::dropIfExists('sms_lists');
    }
}
