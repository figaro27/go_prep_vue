<?php

namespace App\Http\Controllers\Store;

use App\SurveyResponse;
use Illuminate\Http\Request;

class SurveyResponseController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $surveyResponses = SurveyResponse::where(
            'store_id',
            $this->store->id
        )->get();
        $order_number = null;
        $groupedSurveyResponses = [];
        foreach ($surveyResponses as $surveyResponse) {
            if ($order_number !== $surveyResponse->order_number) {
                $groupedSurveyResponses[] = $surveyResponse;
            }
            $order_number = $surveyResponse->order_number;
        }
        return $groupedSurveyResponses;
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SurveyResponse  $surveyResponse
     * @return \Illuminate\Http\Response
     */
    public function show(SurveyResponse $surveyResponse)
    {
    }

    public function getSurveyResponse(Request $request)
    {
        $orderId = $request->orderId;
        return SurveyResponse::where('order_id', $orderId)->get();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SurveyResponse  $surveyResponse
     * @return \Illuminate\Http\Response
     */
    public function edit(SurveyResponse $surveyResponse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SurveyResponse  $surveyResponse
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SurveyResponse $surveyResponse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SurveyResponse  $surveyResponse
     * @return \Illuminate\Http\Response
     */
    public function destroy(SurveyResponse $surveyResponse)
    {
        //
    }
}
