<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_chats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_id');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table->unsignedInteger('chat_id');
            $table->boolean('unread')->default(1);
            $table->string('updatedAt');
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
        Schema::dropIfExists('sms_chats');
    }
}
