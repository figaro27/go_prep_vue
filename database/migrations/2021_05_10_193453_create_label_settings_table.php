<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabelSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('label_settings', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->unsignedInteger('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table->boolean('index')->default(1);
            $table->boolean('nutrition')->default(1);
            $table->boolean('macros')->default(0);
            $table->boolean('logo')->default(1);
            $table->boolean('website')->default(1);
            $table->boolean('social')->default(1);
            $table->boolean('customer')->default(1);
            $table->boolean('description')->default(0);
            $table->boolean('instructions')->default(1);
            $table->boolean('expiration')->default(0);
            $table->boolean('ingredients')->default(0);
            $table->boolean('allergies')->default(0);
            $table->boolean('packaged_by')->default(0);
            $table->boolean('packaged_on')->default(0);
            $table->boolean('daily_order_number')->default(0);
            $table->double('width')->default(4.0);
            $table->double('height')->default(2.33);
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
        Schema::dropIfExists('label_settings');
    }
}
