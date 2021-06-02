<?php

namespace App\Http\Controllers\Store;

use App\SurveyQuestion;
use Illuminate\Http\Request;

class SurveyQuestionController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->surveyQuestions;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $question = $request->question;
        $surveyQuestion = new SurveyQuestion();
        $surveyQuestion->store_id = $this->store->id;
        $surveyQuestion->question = $question['question'];
        $surveyQuestion->context = $question['context'];
        $surveyQuestion->type = $question['type'];
        $surveyQuestion->limit = $question['limit'];

        if (isset($question['condition_question_id'])) {
            $relatedQuestion = SurveyQuestion::where(
                'id',
                $question['condition_question_id']
            )->first();
            $surveyQuestion->context = $relatedQuestion->context;
        } else {
            $relatedQuestion = null;
        }
        if (isset($question['conditional']) && $question['conditional']) {
            $conditional = true;
        } else {
            $conditional = null;
        }

        if (isset($question['min'])) {
            $surveyQuestion->min = $question['min'];
        }
        if (isset($question['max'])) {
            $surveyQuestion->max = $question['max'];
        }
        if (isset($question['options'])) {
            $surveyQuestion->options = $question['options'];
        }
        if ($conditional) {
            $surveyQuestion->conditional = $question['conditional'];
        }

        if ($conditional && isset($question['condition_question_id'])) {
            $surveyQuestion->condition_question_id =
                $question['condition_question_id'];
        }

        if ($conditional && isset($question['condition_value'])) {
            $surveyQuestion->condition_value = $question['condition_value'];
        }

        if (
            $conditional &&
            isset($question['rating_condition']) &&
            $relatedQuestion &&
            $relatedQuestion->type === 'Rating'
        ) {
            $surveyQuestion->rating_condition = $question['rating_condition'];
        }

        $surveyQuestion->save();
        return $surveyQuestion;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SurveyQuestion  $surveyQuestion
     * @return \Illuminate\Http\Response
     */
    public function show(SurveyQuestion $surveyQuestion)
    {
        return $surveyQuestion;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SurveyQuestion  $surveyQuestion
     * @return \Illuminate\Http\Response
     */
    public function edit(SurveyQuestion $surveyQuestion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SurveyQuestion  $surveyQuestion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SurveyQuestion $surveyQuestion)
    {
        $surveyQuestion->update($request->toArray());
        return $surveyQuestion;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SurveyQuestion  $surveyQuestion
     * @return \Illuminate\Http\Response
     */
    public function destroy(SurveyQuestion $surveyQuestion)
    {
        $surveyQuestion->delete();
    }
}
