<?php

namespace App\Http\Controllers;

use App\StoreDetail;
use Illuminate\Http\Request;
use Auth;

class StoreDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\StoreDetail  $storeDetail
     * @return \Illuminate\Http\Response
     */
    public function show(StoreDetail $storeDetail)
    {
        $id = Auth::user()->id;
        $store = StoreDetail::findOrFail($id);
        return $store;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\StoreDetail  $storeDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(StoreDetail $storeDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StoreDetail  $storeDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'logo' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip' => 'required|integer',
            'description' => 'required|string',
        ]);

        $id = auth('api')->user()->id;
        $store = StoreDetail::findOrFail($id);
        $store->update($request->toArray());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StoreDetail  $storeDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(StoreDetail $storeDetail)
    {
        //
    }
}
