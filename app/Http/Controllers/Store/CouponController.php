<?php

namespace App\Http\Controllers\Store;

use App\Store;
use App\Coupon;
use Illuminate\Http\Request;

class CouponController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->coupons;
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
        $props = $props->only([
            'code',
            'type',
            'amount',
            'freeDelivery',
            'oneTime'
        ]);
        $amount = 0;
        if ($props->get('amount') != null) {
            $amount = $props->get('amount');
        }

        $coupon = new Coupon();
        $coupon->store_id = $this->store->id;
        $coupon->code = $props->get('code');
        $coupon->type = $props->get('type');
        $coupon->freeDelivery = $props->get('freeDelivery');
        $coupon->oneTime = $props->get('oneTime');
        $coupon->amount = $amount;
        $coupon->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function show(Coupon $coupon)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function edit(Coupon $coupon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Coupon $coupon)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $coupon = $this->store->coupons()->findOrFail($id);
        $coupon->delete();
    }
}
