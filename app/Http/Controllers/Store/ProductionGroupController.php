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
    public function create(Request $request)
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newGroup = new ProductionGroup();
        $newGroup->title = $request->get('group');
        $newGroup->store_id = $this->store->id;
        $newGroup->save();
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

    public function update(ProductionGroup $productionGroup)
    {
    }

    public function updateProdGroups(Request $request)
    {
        $store = $this->store;
        $group = $store->productionGroups()->findOrFail($request->get('id'));
        $group->title = $request->get('title');
        $group->update();
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
