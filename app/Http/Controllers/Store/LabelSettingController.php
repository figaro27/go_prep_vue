<?php

namespace App\Http\Controllers\Store;

use App\LabelSetting;
use Illuminate\Http\Request;

class LabelSettingController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->labelSettings();
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
     * @param  \App\LabelSetting  $labelSetting
     * @return \Illuminate\Http\Response
     */
    public function show(LabelSetting $labelSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LabelSetting  $labelSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(LabelSetting $labelSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LabelSetting  $labelSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
    }

    public function updateLabelSettings(Request $request)
    {
        $labelSettings = LabelSetting::where('store_id', $this->store->id);
        $values = $request->all();
        $labelSettings->update($values);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LabelSetting  $labelSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(LabelSetting $labelSetting)
    {
        //
    }
}
