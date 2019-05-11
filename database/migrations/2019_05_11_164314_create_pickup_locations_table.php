<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePickupLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pickup_locations', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->integer('store_id')
                ->references('id')
                ->on('stores');
            $table->string('name')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip');
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
        Schema::dropIfExists('pickup_locations');
    }
}
