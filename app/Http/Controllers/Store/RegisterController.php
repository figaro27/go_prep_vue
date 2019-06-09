<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Store\StoreController;
use Illuminate\Http\Request;
use App\Customer;
use App\User;
use Illuminate\Support\Facades\Hash;
use Stripe;

class RegisterController extends StoreController
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
        $user = User::create([
            'user_role_id' => 1,
            'email' => $request->get('email'),
            'password' => Hash::make('secret'),
            'timezone' => 'America/New_York',
            'remember_token' => Hash::make(str_random(10)),
            'accepted_tos' => 1
        ]);

        $userDetails = $user->details()->create([
            'firstname' => $request->get('first_name'),
            'lastname' => $request->get('last_name'),
            'phone' => $request->get('phone'),
            'address' => $request->get('address'),
            'city' => $request->get('city'),
            'state' => $request->get('state'),
            'zip' => $request->get('zip'),
            'delivery' => $request->get('delivery'),
            'country' => 'USA',
            'created_at' => now(),
            'updated_at' => now(),
            'notifications' => array(
                'delivery_today' => true,
                'meal_plan' => true,
                'meal_plan_paused' => true,
                'new_order' => true,
                'subscription_meal_substituted' => true,
                'subscription_renewing' => true
            )
        ]);

        $user = User::orderBy('created_at', 'desc')->first();
        $userId = User::orderBy('created_at', 'desc')
            ->pluck('id')
            ->first();
        $store = $this->store;
        $storeId = $this->store->id;

        $user->createStoreCustomer($storeId);

        /*

        $acct = $store->settings->stripe_account;
        \Stripe\Stripe::setApiKey($acct['access_token']);
        $stripeCustomer = \Stripe\Customer::create([
            'email' => $user->email,
            'description' => $user->name
        ]);
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $customer = new Customer();
        $customer->user_id = $userId;
        $customer->store_id = $storeId;
        $customer->stripe_id = null; //$stripeCustomer->id;
        $customer->currency = $store->settings->currency;
        $customer->save();
        */
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
    }
}
