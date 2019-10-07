<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_attachments', function (Blueprint $table) {
            $table->softDeletes();
            $table->increments('id');
            $table
                ->unsignedInteger('meal_id')
                ->refrences('id')
                ->on('meals');
            $table
                ->unsignedInteger('meal_size_id')
                ->references('id')
                ->on('meal_sizes')
                ->nullable();
            $table
                ->unsignedInteger('attached_meal_id')
                ->refrences('id')
                ->on('meals');
            $table
                ->unsignedInteger('attached_meal_size_id')
                ->references('id')
                ->on('meal_sizes')
                ->nullable();
            $table->integer('quantity')->default(1);
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
        Schema::dropIfExists('meal_attachments');
    }
}
