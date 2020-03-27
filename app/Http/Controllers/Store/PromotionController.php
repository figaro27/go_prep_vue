<?php

namespace App\Http\Controllers\Store;

use App\Promotion;
use Illuminate\Http\Request;

class PromotionController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->promotions;
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
        $data = $request->get('promotion');

        $promotions = Promotion::where('store_id', $this->store->id)->get();
        foreach ($promotions as $promotion) {
            if (
                isset($data['conditionType']) &&
                $promotion->conditionType === $data['conditionType']
            ) {
                return response()->json(
                    [
                        'message' =>
                            'A promotion with the condition type "' .
                            $data['conditionType'] .
                            '" already exists. Please edit the existing promotion instead of adding a duplicate.'
                    ],
                    400
                );
            }
        }

        $promotion = new Promotion();
        $promotion->store_id = $this->store->id;
        $promotion->active = 1;
        $promotion->promotionType = $data['promotionType'];
        $promotion->promotionAmount = $data['promotionAmount'];
        $promotion->freeDelivery = $data['freeDelivery'];
        $promotion->conditionType = isset($data['conditionType'])
            ? $data['conditionType']
            : null;
        $promotion->conditionAmount = isset($data['conditionAmount'])
            ? $data['conditionAmount']
            : null;
        $promotion->pointsName = isset($data['pointsName'])
            ? $data['pointsName']
            : null;
        $promotion->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function show(Promotion $promotion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function edit(Promotion $promotion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Promotion $promotion)
    {
        $data = $request->all();
        if (isset($data['active'])) {
            $promotion->active = $data['active'];
        }

        if (isset($data['promotionType'])) {
            $promotion->promotionType = $data['promotionType'];
        }

        if (isset($data['promotionAmount'])) {
            $promotion->promotionAmount = $data['promotionAmount'];
        }

        if (isset($data['freeDelivery'])) {
            $promotion->freeDelivery = $data['freeDelivery'];
        }

        if (isset($data['conditionType'])) {
            $promotion->conditionType = $data['conditionType'];
        }

        if (isset($data['conditionAmount'])) {
            $promotion->conditionAmount = $data['conditionAmount'];
        }

        $promotion->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
    }
}
