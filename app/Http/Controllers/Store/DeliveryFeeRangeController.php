<?php

namespace App\Http\Controllers\Store;

use App\DeliveryFeeRange;
use Illuminate\Http\Request;

class DeliveryFeeRangeController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->deliveryFeeRanges;
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
        foreach ($request->toArray() as $row) {
            if (isset($row['id'])) {
                $range = DeliveryFeeRange::where('id', $row['id'])->first();
            }

            if (isset($range)) {
                $range->price = $row['price'];
                $range->starting_miles = $row['starting_miles'];
                $range->ending_miles = $row['ending_miles'];
                $range->update();
            } else {
                $range = new DeliveryFeeRange();
                $range->store_id = $this->store->id;
                $range->price = $row['price'];
                $range->starting_miles = $row['starting_miles'];
                $range->ending_miles = $row['ending_miles'];
                $range->save();
            }
            $range = null;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DeliveryFeeRange  $deliveryFeeRange
     * @return \Illuminate\Http\Response
     */
    public function show(DeliveryFeeRange $deliveryFeeRange)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DeliveryFeeRange  $deliveryFeeRange
     * @return \Illuminate\Http\Response
     */
    public function edit(DeliveryFeeRange $deliveryFeeRange)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DeliveryFeeRange  $deliveryFeeRange
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeliveryFeeRange $deliveryFeeRange)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DeliveryFeeRange  $deliveryFeeRange
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $range = DeliveryFeeRange::where('id', $id)->first();
        $range->delete();
    }
}
