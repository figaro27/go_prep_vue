<?php

namespace App\Http\Controllers\Store;

use App\User;
use App\UserDetail;
use App\Customer;
use App\Card;
use App\Order;
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
        $customers = $this->store->customers;
        $customers->makeHidden(['first_order', 'paid_orders']);

        if ($customers && count($customers) > 0) {
            $customers = $customers->unique('user_id');
            return $customers->values();
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
            'paid_orders',
            'phone',
            'address',
            'city',
            'zip',
            'delivery',
            'currency',
            'store_id',
            'created_at',
            'updated_at',
            'joined',
            'added_by_store_id',
            'total_payments',
            'total_paid',
            'state',
            'payment_gateway'
        ]);

        $customers = $customers->last();
        return [$customers];
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

    public function updateCustomerUserDetails(Request $request)
    {
        $customerId = $request->get('id');
        $userDetailId = $request->get('id');
        $details = $request->get('details');
        $customers = $request->get('customers');

        if ($customers) {
            $customer = Customer::where('id', $customerId)->first();
            $userDetail = UserDetail::where(
                'user_id',
                $customer->user_id
            )->first();
            $userDetail->update($details);
            $user = User::where('id', $customer->user_id)->first();
            $user->email = $details['email'];
            $user->save();
        } else {
            $userDetail = UserDetail::where('user_id', $userDetailId)->first();
            $userDetail->update($details);
            $user = User::where('id', $userDetail->user_id)->first();
            $user->email = $request->get('email');
            $user->save();
        }

        //Add Email
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
        $user = User::where('id', $customer->user_id)->first();

        $storeId = $this->store->id;
        $gateway = $this->store->settings->payment_gateway;

        $orders = Order::where([
            'user_id' => $user->id,
            'store_id' => $storeId
        ])->count();

        // Only return cards if the store manually added the user or if the user has ordered from the store.
        if ($user->added_by_store_id === $storeId || $orders > 0) {
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
        } else {
            return [];
        }
    }

    public function searchCustomer(Request $request)
    {
        $query = strtolower($request->get('query'));

        if ($query) {
            $customers = Customer::where('store_id', $this->store->id)
                ->get()
                ->map(function ($customer) use ($query) {
                    if (
                        strpos(strtolower($customer->email), $query) !==
                            false ||
                        strpos(strtolower($customer->firstname), $query) !==
                            false ||
                        strpos(strtolower($customer->lastname), $query) !==
                            false ||
                        strpos(strtolower($customer->phone), $query) !==
                            false ||
                        strpos(strtolower($customer->address), $query) !==
                            false ||
                        strpos(strtolower($customer->city), $query) !== false ||
                        strpos(strtolower($customer->zip), $query) !== false
                    ) {
                        return $customer;
                    }
                });
            return $customers;
        }

        return [];
    }
}
