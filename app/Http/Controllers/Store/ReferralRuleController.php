<?php

namespace App\Http\Controllers\Store;

use App\Store;
use App\ReferralRule;
use Illuminate\Http\Request;

class ReferralRuleController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->referralRules;
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
    public function show(ReferralRule $referralRule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ReferralRule  $referralRule
     * @return \Illuminate\Http\Response
     */
    public function edit(ReferralRule $referralRule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ReferralRule  $referralRule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReferralRule $referralRule)
    {
        $referralRules = ReferralRule::where('store_id', $this->store->id);
        $values = $request->all();
        $referralRules->update($values);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ReferralRule  $referralRule
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReferralRule $referralRule)
    {
        //
    }
}
