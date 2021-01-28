<?php

namespace App\Http\Controllers\Store;

use App\Error;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ErrorController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lastWeek = Carbon::now()
            ->subDays('7')
            ->toDateTimeString();

        $errors = Error::where('store_id', $this->store->id)
            ->where('created_at', '>=', $lastWeek)
            ->with('user')
            ->get();
        $errors = $errors
            ->filter(function ($error) {
                if (
                    strpos($error->error, ' card ') !== false ||
                    strpos($error->error, ' card\'s ') !== false
                ) {
                    return $error;
                }
            })
            ->values();

        return $errors;
    }

    public function getErrorsWithDates(Request $request)
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

        $errors = Error::where('store_id', $this->store->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->with('user')
            ->get();

        $errors = $errors
            ->filter(function ($error) {
                if (
                    strpos($error->error, ' card ') !== false ||
                    strpos($error->error, ' card\'s ') !== false
                ) {
                    return $error;
                }
            })
            ->values();

        return $errors;
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
     * @param  \App\Error  $error
     * @return \Illuminate\Http\Response
     */
    public function show(Error $error)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Error  $error
     * @return \Illuminate\Http\Response
     */
    public function edit(Error $error)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Error  $error
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Error $error)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Error  $error
     * @return \Illuminate\Http\Response
     */
    public function destroy(Error $error)
    {
        //
    }
}
