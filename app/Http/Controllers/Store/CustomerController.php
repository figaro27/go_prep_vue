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
        // return $this->store
        //     ->customers()
        //     ->without(['user', 'orders', 'paid_orders'])
        //     ->get();
        $customers = $this->store->customers->unique('user_id');
        $customers->makeHidden(['first_order', 'paid_orders']);

        if ($customers && count($customers) > 0) {
            return $customers->toArray();
        }

        return [];
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

    public function updateEmail(Request $request)
    {
        $customerId = $request->get('id');
        $customer = Customer::where('id', $customerId)->first();
        $user = User::where('id', $customer->user_id)->first();
        $newEmail = $request->get('email');
        $user->email = $newEmail;
        $user->save();
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
        $customerId = $request->get('id');
        $customer = Customer::where('id', $customerId)->first();

        $storeId = $this->store->id;
        $gateway = $this->store->settings->payment_gateway;

        return $customer->user
            ->cards()
            ->where('payment_gateway', $gateway)
            ->get()
            ->filter(function ($card) use ($storeId, $gateway) {
                if ($gateway === 'authorize') {
                    return $card->store_id === $storeId;
                } else {
                    return true;
                }
            });
    }
}
