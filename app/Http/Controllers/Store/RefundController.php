<?php

namespace App\Http\Controllers\Store;

use App\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RefundController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $twoWeeksAgo = Carbon::now()
            ->subDays('14')
            ->toDateTimeString();

        $refunds = Refund::where('store_id', $this->store->id)
            ->where('created_at', '>=', $twoWeeksAgo)
            ->with(['user', 'card'])
            ->get();

        return $refunds;
    }

    public function getRefundsWithDates(Request $request)
    {
        $startDate = isset($request['start_date'])
            ? Carbon::parse($request['start_date'])
            : null;
        $endDate = isset($request['end_date'])
            ? Carbon::parse($request['end_date'])
            : null;

        if (!$endDate) {
            $endDate = $startDate;
        }

        $refunds = Refund::where('store_id', $this->store->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->with(['user', 'card'])
            ->get();

        return $refunds;
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
     * @param  \App\Refund  $refund
     * @return \Illuminate\Http\Response
     */
    public function show(Refund $refund)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Refund  $refund
     * @return \Illuminate\Http\Response
     */
    public function edit(Refund $refund)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Refund  $refund
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Refund $refund)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Refund  $refund
     * @return \Illuminate\Http\Response
     */
    public function destroy(Refund $refund)
    {
        //
    }
}
