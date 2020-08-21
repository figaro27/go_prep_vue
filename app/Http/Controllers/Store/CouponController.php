<?php

namespace App\Http\Controllers\Store;

use App\Store;
use App\Coupon;
use Illuminate\Http\Request;
use App\Http\Requests\CouponRequest;

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

    public function findCoupon(Request $request)
    {
        $storeId = $request->get('store_id');
        $couponCode = $request->get('couponCode');
        $coupon = Coupon::where([
            'store_id' => $storeId,
            'code' => $couponCode
        ])->first();
        if (isset($coupon)) {
            return $coupon;
        }
    }

    public function findCouponById(Request $request)
    {
        $storeId = $request->get('store_id');
        $couponId = $request->get('couponId');
        $coupon = Coupon::where([
            'store_id' => $storeId,
            'id' => $couponId
        ])->first();
        if (isset($coupon)) {
            return $coupon;
        }
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
    public function store(CouponRequest $request)
    {
        $props = collect($request->all());
        $props = $props->only([
            'code',
            'type',
            'amount',
            'freeDelivery',
            'oneTime',
            'minimum'
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
        $coupon->minimum = $props->has('minimum') ? $props->get('minimum') : 0;
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
        $coupon = Coupon::where('id', $request->get('id'))->first();
        $referredUser = $coupon->referral_user_id;
        $coupon->referral_user_id = $request->get('referral_user_id');
        $coupon->update();
        return $referredUser;
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
