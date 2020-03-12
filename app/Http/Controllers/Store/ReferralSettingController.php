<?php

namespace App\Http\Controllers\Store;

use App\Store;
use App\ReferralSetting;
use Illuminate\Http\Request;

class ReferralSettingController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->referralSettings;
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
     * @param  \App\ReferralRule  $referralRule
     * @return \Illuminate\Http\Response
     */
    public function show(ReferralSetting $referralSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ReferralRule  $referralRule
     * @return \Illuminate\Http\Response
     */
    public function edit(ReferralSetting $referralSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ReferralSetting  $referralRule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReferralSetting $referralSetting)
    {
        $referralSettings = ReferralSetting::where(
            'store_id',
            $this->store->id
        );
        $values = $request->except(['amountFormat', 'url', 'store']);
        $referralSettings->update($values);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ReferralSetting  $referralRule
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReferralSetting $referralSetting)
    {
        //
    }
}
