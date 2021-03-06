<?php

namespace App\Http\Controllers\Store;

use App\OrderLabelSetting;
use Illuminate\Http\Request;

class OrderLabelSettingController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->orderLabelSettings();
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
     * @param  \App\OrderLabelSetting  $orderLabelSetting
     * @return \Illuminate\Http\Response
     */
    public function show(OrderLabelSetting $orderLabelSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\OrderLabelSetting  $orderLabelSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderLabelSetting $orderLabelSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\OrderLabelSetting  $orderLabelSetting
     * @return \Illuminate\Http\Response
     */
    public function update(
        Request $request,
        OrderLabelSetting $orderLabelSetting
    ) {
        //
    }

    public function updateOrderLabelSettings(Request $request)
    {
        $orderLabelSetting = OrderLabelSetting::where(
            'store_id',
            $this->store->id
        );
        $values = $request->all();
        $orderLabelSetting->update($values);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\OrderLabelSetting  $orderLabelSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderLabelSetting $orderLabelSetting)
    {
        //
    }
}
