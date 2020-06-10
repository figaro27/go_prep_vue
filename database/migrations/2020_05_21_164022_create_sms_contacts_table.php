<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSMSContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_id');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table->unsignedInteger('contact_id');
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
        Schema::dropIfExists('sms_contacts');
    }
}
