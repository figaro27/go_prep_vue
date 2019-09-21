<?php

namespace App\Http\Controllers\Store;

use App\Store;
use App\LineItem;
use Illuminate\Http\Request;

class LineItemController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->lineItems();
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
        $props = collect($request->all());
        $props = $props->only(['order_id', 'title', 'price']);

        $lineItem = new LineItem();
        $lineItem->store_id = $this->store->id;
        $lineItem->title = $props->get('title');
        $lineItem->price = $props->get('price');
        $lineItem->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LineItem  $lineItem
     * @return \Illuminate\Http\Response
     */
    public function show(LineItem $lineItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LineItem  $lineItem
     * @return \Illuminate\Http\Response
     */
    public function edit(LineItem $lineItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LineItem  $lineItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LineItem $lineItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LineItem  $lineItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(LineItem $lineItem)
    {
        $lineItem = $this->store->lineItems()->findOrFail($id);
        $lineItem->delete();
    }
}
