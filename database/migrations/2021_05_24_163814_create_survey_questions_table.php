<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->unsignedInteger('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table->string('question');
            $table->string('context')->default('order');
            $table->string('type')->default('Text');
            $table->boolean('limit')->default(1);
            $table->unsignedInteger('max')->default(10);
            $table->json('options')->nullable();
            $table->boolean('conditional')->default(0);
            $table->unsignedInteger('condition_question_id')->nullable();
            $table->string('rating_condition')->nullable();
            $table->string('condition_value')->nullable();
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
        Schema::dropIfExists('survey_questions');
    }
}
