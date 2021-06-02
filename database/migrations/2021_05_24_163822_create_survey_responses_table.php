<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->unsignedInteger('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->unsignedInteger('user_id')
                ->references('id')
                ->on('users');
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->string('customer_name')->nullable();
            $table
                ->unsignedInteger('order_id')
                ->references('id')
                ->on('orders');
            $table
                ->foreign('order_id')
                ->references('id')
                ->on('orders');
            $table->string('order_number');
            $table->dateTime('delivery_date')->nullable();
            $table
                ->unsignedInteger('survey_question_id')
                ->references('id')
                ->on('survey_questions');
            $table
                ->foreign('survey_question_id')
                ->references('id')
                ->on('survey_questions');
            $table->longText('survey_question')->nullable();
            $table->longtext('item_name')->nullable();
            $table->longtext('response')->nullable();
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
        Schema::dropIfExists('survey_responses');
    }
}
