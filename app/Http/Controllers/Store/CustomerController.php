<?php

namespace App\Http\Controllers\Store;

use App\Customer;
use App\Order;
use App\User;
use App\UserDetail;
use Illuminate\Http\Request;

class CustomerController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Not returning customers for Livoti's until customer pagination is in place
        if (
            $this->store->id == 108 ||
            $this->store->id == 109 ||
            $this->store->id == 110
        ) {
            return [];
        }
        $customers = $this->store->customers;
        $customers->makeHidden(['first_order', 'paid_orders']);

        if ($customers && count($customers) > 0) {
            $customers = $customers->unique('user_id');
            return $customers->values();
        }
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

        $store = $this->store;
        $gateway = $this->store->settings->payment_gateway;

        $orders = Order::where([
            'user_id' => $user->id,
            'store_id' => $store->id
        ])->count();

        $added_by_store_id = $user->added_by_store_id;
        $last_viewed_store_id = $user->last_viewed_store_id;
        $shared_store_ids = explode(",", $store->shared_store_ids);
        $shared = false;

        if ($shared_store_ids[0] !== "") {
            if (
                in_array($added_by_store_id, $shared_store_ids) ||
                in_array($last_viewed_store_id, $shared_store_ids)
            ) {
                $shared = true;
            }
        }

        // Only return cards if the store manually added the user or if the user has ordered from the store.
        if ($added_by_store_id === $store->id || $orders > 0 || $shared) {
            return $customer->user
                ->cards()
                ->where('payment_gateway', $gateway)
                ->get()
                ->filter(function ($card) use ($store, $gateway) {
                    if ($gateway === 'authorize') {
                        return $card->store_id === $store->id;
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
        $queryEscaped = addslashes(str_replace('@', ' ', $query));

        if ($query) {
            $customers = Customer::where('store_id', $this->store->id)
                ->whereHas('user', function ($q) use ($query, $queryEscaped) {
                    $q
                        ->where('email', 'LIKE', "%$query%")
                        ->orWhereHas('userDetail', function ($q) use (
                            $query,
                            $queryEscaped
                        ) {
                            $q->whereRaw(
                                "MATCH(firstname, lastname, phone, address) AGAINST('*{$queryEscaped}*' IN BOOLEAN MODE)"
                            );
                        });
                })
                ->get();

            return $customers;
        }

        return [];
    }

    public function getDistanceFrom(Request $request)
    {
        $customerId = $request->get('id');
        $customer = Customer::where('id', $customerId)->first();
        $user = User::where('id', $customer->user_id)->first();
        $distance = $user->distanceFrom($this->store);
        return $distance;
    }
}
