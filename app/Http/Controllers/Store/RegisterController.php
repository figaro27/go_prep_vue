<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Store\StoreController;
use Illuminate\Http\Request;
use App\Customer;
use App\User;
use Illuminate\Support\Facades\Hash;
use Stripe;
use App\Billing\Authorize;
use App\UserDetail;

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
    public function createStripeOrAuthorizeId($store, $user, $name)
    {
        $gateway = $store->settings->payment_gateway;
        if ($gateway === 'stripe') {
            $acct = $store->settings->stripe_account;
            \Stripe\Stripe::setApiKey($acct['access_token']);
            $stripeCustomer = \Stripe\Customer::create([
                'email' => $user->email,
                'description' => $name
            ]);
            $gatewayCustomerId = $stripeCustomer->id;
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            return $stripeCustomer->id;
        } elseif ($gateway === 'authorize') {
            $authorize = new Authorize($store);
            $gatewayCustomerId = $authorize->createCustomer($user);
            return $gatewayCustomerId;
        }
    }

    public function checkIfCustomerExists(
        $email,
        $phone,
        $zip,
        $storeId,
        $request
    ) {
        // Try phone first
        $customer = Customer::where([
            'phone' => $phone,
            'store_id' => $storeId
        ])->first();
        // Then email
        if (!$customer) {
            $customer = Customer::where([
                'email' => $email,
                'store_id' => $storeId
            ])->first();
        }

        if ($customer) {
            // Update the existing customer with newly entered information (each customer is tied to their own store so this is okay). The main user & user details remain unaffected.
            $customer->firstname = $request['firstname'];
            $customer->lastname = $request['lastname'];
            $customer->name =
                $request['firstname'] . ' ' . $request['lastname'];
            $customer->phone = $phone;
            $customer->address = $request['address']
                ? $request['address']
                : 'N/A';
            $customer->city = $request['city'] ? $request['city'] : 'N/A';
            $customer->state = $request['state'] ? $request['state'] : null;
            $customer->zip = $zip;
            $customer->delivery = $request['delivery']
                ? $request['delivery']
                : 'N/A';
            $customer->update();
            return $customer;
        } else {
            return null;
        }
    }

    public function store(Request $request)
    {
        $store = $this->store;
        $email = $request->get('email')
            ? $request->get('email')
            : 'noemail-' .
                substr(uniqid(rand(10, 99), false), 0, 12) .
                '@goprep.com';
        $phone = $request->get('phone');
        $password = $request->get('password');

        $zip = $request->get('zip') ? $request->get('zip') : 'N/A';
        if ($store->details->country == 'US') {
            $zip = substr($zip, 0, 5);
        }

        $name = $request->get('firstname') . ' ' . $request->get('lastname');

        $existingCustomer = $this->checkIfCustomerExists(
            $email,
            $phone,
            $zip,
            $store->id,
            $request
        );
        if ($existingCustomer) {
            return [
                'text' => $existingCustomer->name,
                'value' => $existingCustomer->id
            ];
        }

        // See if a user exists with either the entered email or phone number

        $user = User::where('email', $email)->first();
        if (!$user) {
            $userId = UserDetail::where('phone', $phone)
                ->pluck('user_id')
                ->first();
            if ($userId) {
                $user = User::where('id', $userId)
                    ->with('details')
                    ->first();
            }
        }

        if (!$user) {
            // If the user doesn't exist, add a new user record, new user detail record, and then add them as a new customer
            $user = User::create([
                'user_role_id' => 1,
                'email' => $email,
                'password' => $password
                    ? bcrypt($password)
                    : Hash::make(str_random(10)),
                'timezone' => 'America/New_York',
                'remember_token' => Hash::make(str_random(10)),
                'accepted_tos' => 1,
                'added_by_store_id' => $store->id,
                'referralUrlCode' =>
                    'R' .
                    strtoupper(substr(uniqid(rand(10, 99), false), -4)) .
                    chr(rand(65, 90)) .
                    rand(0, 9) .
                    rand(0, 9) .
                    chr(rand(65, 90))
            ]);

            $userDetails = $user->details()->create([
                'companyname' => $request->get('company_name'),
                'email' => $email,
                'firstname' => $request->get('firstname'),
                'lastname' => $request->get('lastname'),
                'phone' => $phone,
                'address' => $request->get('address')
                    ? $request->get('address')
                    : 'N/A',
                'city' => $request->get('city') ? $request->get('city') : 'N/A',
                'state' => $request->get('state')
                    ? $request->get('state')
                    : null,
                'zip' => $zip,
                'delivery' => $request->get('delivery')
                    ? $request->get('delivery')
                    : 'N/A',
                'country' => $store->details->country,
                'created_at' => now(),
                'updated_at' => now(),
                'notifications' => array(
                    'delivery_today' => true,
                    'meal_plan' => true,
                    'meal_plan_paused' => true,
                    'new_order' => true,
                    'subscription_meal_substituted' => true,
                    'subscription_renewing' => true,
                    'new_referral' => true
                )
            ]);
        }

        $customer = new Customer();
        $customer->store_id = $store->id;
        $customer->user_id = $user->id;
        $customer->stripe_id = $this->createStripeOrAuthorizeId(
            $store,
            $user,
            $name
        );
        $customer->firstname = $request['firstname'];
        $customer->lastname = $request['lastname'];
        $customer->name = $name;
        $customer->phone = $phone;
        $customer->address = $request['address'] ? $request['address'] : 'N/A';
        $customer->city = $request['city'] ? $request['city'] : 'N/A';
        $customer->state = $request['state'] ? $request['state'] : null;
        $customer->zip = $zip;
        $customer->delivery = $request['delivery']
            ? $request['delivery']
            : 'N/A';
        $customer->save();

        return [
            'text' => $customer->name,
            'value' => $customer->id
        ];
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
        $customerId = Customer::where([
            'user_id' => $user->id,
            'store_id' => $storeId
        ])
            ->pluck('id')
            ->first();
        $customer = [
            'id' => $customerId,
            'name' => $user->name,
            'existing' => true
        ];
        return $customer;
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
