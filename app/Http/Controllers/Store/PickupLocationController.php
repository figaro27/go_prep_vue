<?php

namespace App\Http\Controllers\Store;

use App\Store;
use App\PickupLocation;
use Illuminate\Http\Request;

class PickupLocationController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->pickupLocations;
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
     * @param  \App\PickupLocation  $pickupLocation
     * @return \Illuminate\Http\Response
     */
    public function show(PickupLocation $pickupLocation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PickupLocation  $pickupLocation
     * @return \Illuminate\Http\Response
     */
    public function edit(PickupLocation $pickupLocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PickupLocation  $pickupLocation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PickupLocation $pickupLocation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PickupLocation  $pickupLocation
     * @return \Illuminate\Http\Response
     */
    public function destroy(PickupLocation $pickupLocation)
    {
        //
    }
}
