<?php

namespace App\Http\Controllers\Store;

use App\DeliveryFeeZipCode;
use Illuminate\Http\Request;

class DeliveryFeeZipCodeController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->deliveryFeeZipCodes();
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
     * @param  \App\DeliveryFeeZipCode  $deliveryFeeZipCode
     * @return \Illuminate\Http\Response
     */
    public function show(DeliveryFeeZipCode $deliveryFeeZipCode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DeliveryFeeZipCode  $deliveryFeeZipCode
     * @return \Illuminate\Http\Response
     */
    public function edit(DeliveryFeeZipCode $deliveryFeeZipCode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DeliveryFeeZipCode  $deliveryFeeZipCode
     * @return \Illuminate\Http\Response
     */
    public function updateDeliveryFeeZipCodes(Request $request)
    {
        $rows = $request->all();

        foreach ($rows as $row) {
            $dfzc = DeliveryFeeZipCode::where([
                'zip_code' => $row['zip_code'],
                'store_id' => $this->store->id
            ])->first();
            if ($dfzc) {
                $dfzc->delivery_fee = $row['delivery_fee'];
                $dfzc->update();
            } else {
                $dfzc = new DeliveryFeeZipCode();
                $dfzc->store_id = $this->store->id;
                $dfzc->zip_code = $row['zip_code'];
                $dfzc->delivery_fee = $row['delivery_fee'];
                $dfzc->save();
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DeliveryFeeZipCode  $deliveryFeeZipCode
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeliveryFeeZipCode $deliveryFeeZipCode)
    {
        //
    }
}
