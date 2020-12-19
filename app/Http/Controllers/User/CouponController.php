<?php

namespace App\Http\Controllers\User;
use App\Coupon;

use Illuminate\Http\Request;
use App\Subscription;

class CouponController extends UserController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function findCoupon(Request $request)
    {
        $storeId = $request->get('store_id');
        $couponCode = $request->get('couponCode');
        $coupon = Coupon::where([
            'store_id' => $storeId,
            'code' => $couponCode,
            'active' => 1
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
    public function store(Request $request)
    {
        return Coupon::where('store_id', $request->get('store_id'))->get();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
