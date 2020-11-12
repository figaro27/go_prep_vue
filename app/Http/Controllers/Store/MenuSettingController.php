<?php

namespace App\Http\Controllers\Store;

use App\MenuSetting;
use Illuminate\Http\Request;

class MenuSettingController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->menuSettings;
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
     * @param  \App\MenuSetting  $menuSetting
     * @return \Illuminate\Http\Response
     */
    public function show(MenuSetting $menuSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\MenuSetting  $menuSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(MenuSetting $menuSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MenuSetting  $menuSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MenuSetting $menuSetting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\MenuSetting  $menuSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(MenuSetting $menuSetting)
    {
        //
    }
}
