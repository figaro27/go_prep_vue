<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_id');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table->boolean('lab_nutrition')->default(1);
            $table->boolean('lab_macros')->default(0);
            $table->boolean('lab_logo')->default(1);
            $table->boolean('lab_website')->default(1);
            $table->boolean('lab_social')->default(1);
            $table->boolean('lab_customer')->default(1);
            $table->boolean('lab_description')->default(0);
            $table->boolean('lab_instructions')->default(1);
            $table->boolean('lab_expiration')->default(1);
            $table->string('lab_size')->default('4" x 6"');
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
        Schema::dropIfExists('report_settings');
    }
}
