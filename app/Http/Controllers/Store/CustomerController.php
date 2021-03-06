<?php

namespace App\Http\Controllers\Store;

use App\Customer;
use App\Order;
use App\User;
use App\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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
        // if (
        //     $this->store->id == 108 ||
        //     $this->store->id == 109 ||
        //     $this->store->id == 110
        // ) {
        //     return [];
        // }
        $customers = $this->store->customers;
        $customers->makeHidden(['paid_orders']);

        if ($customers && count($customers) > 0) {
            $customers = $customers->unique('user_id');
            return $customers->values();
        } else {
            return [];
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
        $customerId = $request->route()->parameter('customer');
        $customer = Customer::where('id', $customerId)
            ->with('orders')
            ->first();
        return $customer;

        // Removed 5/22/21
        // $id = $request->route()->parameter('customer');
        // $customer = Customer::where('id', $id)->first();
        // $customers = Customer::where([
        //     'user_id' => $customer->user_id,
        //     'store_id' => $this->store->id
        // ])->get();
        // $orders = [];
        // foreach ($customers as $customer) {
        //     array_push($orders, $customer->orders()->get());
        // }
        // $orders = Arr::collapse($orders);
        // $customer->orders = $orders;

        // $userId = Customer::where('id', $id)
        //     ->pluck('user_id')
        //     ->first();
        // $orders = [];
        // $customers = Customer::where('user_id', $userId)
        //     ->with('orders')
        //     ->get();
        // foreach ($customers as $customer) {
        //     array_push($orders, $customer->orders()->get());
        // }
        // $orders = Arr::collapse($orders);

        // $customer = $this->store
        //     ->customers()
        //     ->without(['user'])
        //     ->find($id);

        // $customer->orders = $orders;

        return $customer;
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
        $details = $request->get('details');
        $details['store_id'] = $this->store->id;
        $customers = $request->get('customers');

        if ($customers) {
            $customer = Customer::where('id', $customerId)->first();
            if ($customer) {
                $customer->update($details);
                $customer->email = $details['email'] ?? null;
                $customer->update();
            }
            // No longer updating user details. Customer updating allowed only. Details associated with specific Orders are now in the Orders table.
            // $userDetail = UserDetail::where(
            //     'user_id',
            //     $customer->user_id
            // )->first();
            // $userDetail->update($details);
            $user = User::where('id', $customer->user_id)->first();
            $user->email = $details['email'];
            $user->save();
        } else {
            // $userDetail = UserDetail::where('id', $details['id'])->first();
            $customer = Customer::where(
                'id',
                $request->get('customerId')
            )->first();
            if ($customer) {
                $customer->update($details);
                $customer->email = $details['email'] ?? null;
                $customer->update();
            }
            // $userDetail->update($details);
            $user = User::where('id', $details['user_id'])->first();
            $user->email = $request->get('email');
            $user->save();
        }

        if (isset($details['password'])) {
            $user->password = bcrypt($details['password']);
            $user->update();
        }

        foreach ($customer->orders as $order) {
            $order->customer_name = $customer->name;
            $order->update();
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
        if (!$customer) {
            return [];
        }
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
        // if ($added_by_store_id === $store->id || $orders > 0 || $shared) {

        // ^ Removing the above. Now access is given to the card even if the customer didn't place an order
        if (
            $added_by_store_id === $store->id ||
            $last_viewed_store_id === $store->id ||
            $orders > 0 ||
            $shared
        ) {
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
        $query = str_replace("(", "", $query);
        $query = str_replace(")", "", $query);
        $queryEscaped = addslashes(str_replace('@', ' ', $query));

        if ($query) {
            $allCustomers = Customer::where('store_id', $this->store->id);
            $customers = $allCustomers
                ->where('name', 'LIKE', '%' . $query . '%')
                ->get();
            if (count($customers) > 0) {
                return $customers;
            }
            $allCustomers = Customer::where('store_id', $this->store->id);
            $customers = $allCustomers
                ->where('email', 'LIKE', '%' . $query . '%')
                ->get();
            if (count($customers) > 0) {
                return $customers;
            }
            $allCustomers = Customer::where('store_id', $this->store->id);
            $customers = $allCustomers
                ->where('phone', 'LIKE', '%' . $query . '%')
                ->get();
            if (count($customers) > 0) {
                return $customers;
            }
            $allCustomers = Customer::where('store_id', $this->store->id);
            $customers = $allCustomers
                ->where('address', 'LIKE', '%' . $query . '%')
                ->get();
            if (count($customers) > 0) {
                return $customers;
            }
            $allCustomers = Customer::where('store_id', $this->store->id);
            $customers = $allCustomers
                ->where('firstname', 'LIKE', '%' . $query . '%')
                ->get();
            if (count($customers) > 0) {
                return $customers;
            }
            $allCustomers = Customer::where('store_id', $this->store->id);
            $customers = $allCustomers
                ->where('lastname', 'LIKE', '%' . $query . '%')
                ->get();
            if (count($customers) > 0) {
                return $customers;
            }
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
