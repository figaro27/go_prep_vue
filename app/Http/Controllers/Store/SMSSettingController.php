<?php

namespace App\Http\Controllers\Store;

use App\SMSSetting;
use Illuminate\Http\Request;

class SMSSettingController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->smsSettings();
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
     * @param  \App\SMSSetting  $sMSSetting
     * @return \Illuminate\Http\Response
     */
    public function show(SMSSetting $sMSSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SMSSetting  $sMSSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(SMSSetting $sMSSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SMSSetting  $sMSSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $settings = $request->get('settings');
        $smsSettings = SMSSetting::where('store_id', $this->store->id)->first();
        $smsSettings->update($settings);
        return $smsSettings;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SMSSetting  $sMSSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(SMSSetting $sMSSetting)
    {
        //
    }
}
