<?php

namespace App\Http\Controllers\Store;

use App\User;
use App\Customer;
use App\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store
            ->customers()
            ->without(['user'])
            ->get();
    }

    public function customersNoOrders()
    {
        $customers = $this->store->customers;
        $customers->makeHidden([
            'first_order',
            'last_order',
            'total_payments',
            'total_paid',
            'paid_orders'
        ]);
        return $customers;
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id = $request->route()->parameter('customer');
        return $this->store
            ->customers()
            ->with('orders')
            ->without(['user'])
            ->find($id);
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }

    public function getCards(Request $request)
    {
        /*$customerId = $request->get('id');
        $userId = $this->store->customers
            ->where('id', $customerId)
            ->pluck('user_id')
            ->first();*/

        $customerId = $request->get('id');
        $userId = Customer::where('id', $customerId)
            ->pluck('user_id')
            ->first();
        $card = Card::where('user_id', $userId)->get();

        return $card;
    }
}
