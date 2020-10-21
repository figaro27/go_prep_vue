<?php

namespace App\Http\Controllers;

use App\HolidayTransferTime;
use Illuminate\Http\Request;

class HolidayTransferTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->holidayTransferTimes;
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
     * @param  \App\HolidayTransferTime  $HolidayTransferTime
     * @return \Illuminate\Http\Response
     */
    public function show(HolidayTransferTime $HolidayTransferTime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\HolidayTransferTime  $HolidayTransferTime
     * @return \Illuminate\Http\Response
     */
    public function edit(HolidayTransferTime $HolidayTransferTime)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\HolidayTransferTime  $HolidayTransferTime
     * @return \Illuminate\Http\Response
     */
    public function update(
        Request $request,
        HolidayTransferTime $HolidayTransferTime
    ) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\HolidayTransferTime  $HolidayTransferTime
     * @return \Illuminate\Http\Response
     */
    public function destroy(HolidayTransferTime $HolidayTransferTime)
    {
        //
    }
}
