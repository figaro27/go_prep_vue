<?php

namespace App\Http\Controllers\Store;

use App\StoreModule;
use Illuminate\Http\Request;
use Auth;

class StoreModuleController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->modules;
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
     * @param  \App\StoreModule  $storeModule
     * @return \Illuminate\Http\Response
     */
    public function show(StoreModule $storeModule)
    {
        $id = Auth::user()->id;
        $modules = StoreModule::findOrFail($id);
        return $modules;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\StoreModule  $storeModule
     * @return \Illuminate\Http\Response
     */
    public function edit(StoreModule $storeModule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StoreModule  $storeModule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StoreModule $storeModule)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StoreModule  $storeModule
     * @return \Illuminate\Http\Response
     */
    public function destroy(StoreModule $storeModule)
    {
        //
    }
}
