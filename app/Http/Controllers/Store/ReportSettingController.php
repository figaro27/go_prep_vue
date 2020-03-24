<?php

namespace App\Http\Controllers\Store;

use App\ReportSetting;
use Illuminate\Http\Request;

class ReportSettingController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->reportSettings;
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
     * @param  \App\ReportSetting  $reportSetting
     * @return \Illuminate\Http\Response
     */
    public function show(ReportSetting $reportSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ReportSetting  $reportSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(ReportSetting $reportSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ReportSetting  $reportSetting
     * @return \Illuminate\Http\Response
     */

    public function updateReportSettings(Request $request)
    {
        $reportSettings = ReportSetting::where('store_id', $this->store->id);
        $values = $request->all();
        $reportSettings->update($values);
    }

    public function update(Request $request, ReportSetting $reportSetting)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ReportSetting  $reportSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReportSetting $reportSetting)
    {
        //
    }
}
