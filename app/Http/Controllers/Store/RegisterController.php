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
        $user = User::where('email', $request->get('email'))->first();
        $store = $this->store;
        $storeId = $this->store->id;
        $email = $request->get('email')
            ? $request->get('email')
            : 'noemail-' .
                substr(uniqid(rand(10, 99), false), 0, 12) .
                '@goprep.com';

        if (!$user) {
            $user = User::create([
                'user_role_id' => 1,
                'email' => $email,
                'password' => Hash::make(str_random(10)),
                'timezone' => 'America/New_York',
                'remember_token' => Hash::make(str_random(10)),
                'accepted_tos' => 1,
                'added_by_store_id' => $storeId
            ]);

            $userDetails = $user->details()->create([
                'firstname' => $request->get('first_name'),
                'lastname' => $request->get('last_name'),
                'phone' => $request->get('phone'),
                'address' => $request->get('address')
                    ? $request->get('address')
                    : 'N/A',
                'city' => $request->get('city') ? $request->get('city') : 'N/A',
                'state' => $request->get('state')
                    ? $request->get('state')['value']
                    : 'N/A',
                'zip' => $request->get('zip') ? $request->get('zip') : 'N/A',
                'delivery' => $request->get('delivery')
                    ? $request->get('delivery')
                    : 'N/A',
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
        }

        $user = User::findOrFail($user->id);
        $user->createStoreCustomer(
            $storeId,
            $store->settings->currency,
            $store->settings->payment_gateway
        );

        return Customer::where('user_id', $user->id)
            ->pluck('id')
            ->first();

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

    public function checkExistingCustomer(Request $request)
    {
        $email = $request->get('email');
        $user = User::where('email', $email)->first();
        $store = $this->store;
        $storeId = $this->store->id;
        $existingCustomers = Customer::where('store_id', $storeId)->get();

        if ($user) {
            foreach ($existingCustomers as $existingCustomer) {
                if ($existingCustomer['user_id'] === $user->id) {
                    return 'existsCustomer';
                }
            }
            return 'existsNoCustomer';
        }
        return 'noCustomer';
    }

    public function addExistingCustomer(Request $request)
    {
        $email = $request->get('email');
        $user = User::where('email', $email)->first();
        $user = User::findOrFail($user->id);
        $store = $this->store;
        $storeId = $this->store->id;
        $currency = $this->store->settings->currency;
        $gateway = $this->store->settings->payment_gateway;
        $user->createStoreCustomer($storeId, $currency, $gateway);
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
