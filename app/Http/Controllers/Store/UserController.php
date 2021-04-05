<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Store\StoreController;
use App\Order;
use App\User;
use App\UserDetail;
use Illuminate\Http\Request;

class UserController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $email = auth('api')->user()->email;
        return $email;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'sometimes|min:6',
            'password2' => 'same:password'
        ]);

        $user = auth('api')->user();

        if ($request->has('email') && $request->has('password')) {
            $user->email = $request->get('email');
            $user->password = bcrypt($request->get('password'));
            $user->save();
        } else {
            return 'error';
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }

    public function getLeads()
    {
        return User::where('last_viewed_store_id', $this->store->id)
            ->whereDoesntHave('orders')
            ->get();

        // $users = UserDetail::where('last_viewed_store_id', $this->store->id)
        //     ->where('total_payments', 0)
        //     ->where('multiple_store_orders', 0)
        //     ->get();

        // $multipleStoreOrderUsers = UserDetail::where(
        //     'last_viewed_store_id',
        //     $this->store->id
        // )
        //     ->where('total_payments', '>=', 1)
        //     ->where('multiple_store_orders', 1)
        //     ->get();

        // // The user could have created orders but on a different store
        // foreach ($multipleStoreOrderUsers as $userDetail) {
        //     $addToList = true;
        //     foreach ($userDetail->user->orders as $order) {
        //         if ($order->store_id === $this->store->id) {
        //             $addToList = false;
        //         }
        //     }
        //     if ($addToList) {
        //         $users->push($userDetail);
        //     }
        // }
        // return $users;
    }
}
