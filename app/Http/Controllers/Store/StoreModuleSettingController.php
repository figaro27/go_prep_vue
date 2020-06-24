<?php

namespace App\Http\Controllers\Store;
use App\StoreModuleSettings;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class StoreModuleSettingController extends StoreController
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $moduleSetting = StoreModuleSettings::where(
            'store_id',
            $this->store->id
        )->first();
        $values = $request->except(['omittedTransferTimes', 'store']);
        $moduleSetting->update($values);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function submitMultiAuthPassword(Request $request)
    {
        $moduleSetting = StoreModuleSettings::where(
            'store_id',
            $this->store->id
        )->first();

        $hashedPW = $moduleSetting->multiAuthPassword;

        if (Hash::check($request->get('password'), $hashedPW)) {
            return 1;
        }
        return 0;
    }
}
