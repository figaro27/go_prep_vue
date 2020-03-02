<?php

namespace App\Http\Controllers\Store;

use App\Store;
use App\ProductionGroup;
use Illuminate\Http\Request;

class ProductionGroupController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->productionGroups;
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
     * @param  \App\ProductionGroup  $productionGroup
     * @return \Illuminate\Http\Response
     */
    public function show(ProductionGroup $productionGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ProductionGroup  $productionGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductionGroup $productionGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ProductionGroup  $productionGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductionGroup $productionGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ProductionGroup  $productionGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductionGroup $productionGroup)
    {
        //
    }
}
