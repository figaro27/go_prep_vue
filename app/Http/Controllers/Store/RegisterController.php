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
            $gatewayCustomerId = $authorize->createCustomer($this);
            return $gatewayCustomerId;
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
        $password = $request->get('password');

        $zip = $request->get('zip') ? $request->get('zip') : 'N/A';
        if ($store->details->country == 'US') {
            $zip = substr($zip, 0, 5);
        }

        // See if a user exists with either the entered email or phone number

        $user = User::where('email', $email)->first();
        if (!$user) {
            $userId = UserDetail::where('phone', $request->get('phone'))
                ->pluck('user_id')
                ->first();
            if ($userId) {
                $user = User::where('id', $userId)
                    ->with('details')
                    ->first();
            }
        }
        // Removing for now - 5/22. Instead if the user exists, update the user with new information, and return existing associated customer.
        if ($user) {
            // Check for existing customer for this user and store
            if (
                !$user->hasStoreCustomer(
                    $store->id,
                    $store->settings->currency,
                    $store->settings->payment_gateway
                )
            ) {
                $name =
                    $request->get('firstname') .
                    ' ' .
                    $request->get('lastname');
                // If none then make a new one
                $customer = new Customer();
                $customer->store_id = $store->id;
                $customer->stripe_id = $this->createStripeOrAuthorizeId(
                    $store,
                    $user,
                    $name
                );
                $customer->currency = $store->settings->currency;
                $customer->payment_gateway = $store->settings->payment_gateway;
                $customer->email = $user->email;
                $customer->firstname = $request->get('firstname');
                $userDetails->lastname = $request->get('lastname');
                $customer->phone = $request->get('phone');
                $customer->address = $request->get('address')
                    ? $request->get('address')
                    : 'N/A';
                $customer->city = $request->get('city')
                    ? $request->get('city')
                    : 'N/A';
                $customer->state = $request->get('state')
                    ? $request->get('state')
                    : null;
                $customer->zip = $zip;
                $customer->delivery = $request->get('delivery')
                    ? $request->get('delivery')
                    : 'N/A';
                $customer->country = $store->details->country;
                $customer->save();
            }

            $customer = $user->getStoreCustomer(
                $store->id,
                $store->settings->currency,
                $store->settings->payment_gateway
            );

            // return new or existing customer
            return [
                'text' => $customer->name,
                'value' => $customer->id
            ];
        }
        // // If there's an existing user, check if the home store of that user is trying to add it again and if so, block them
        // if ($user) {
        //     // Add check for last_viewed_store_id also?
        //     if ($user->added_by_store_id === $store->id) {
        //         return response()->json(
        //             [
        //                 'message' =>
        //                     'A user with this email address or phone already exists. Please exit out of this window and search the customer by name or phone number'
        //             ],
        //             400
        //         );
        //     }
        // }
        else {
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
                'phone' => $request->get('phone'),
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

        // If not blocked by an existing user being added again by the home store, add the customer
        $customer = $user->createStoreCustomer(
            $store->id,
            $store->settings->currency,
            $store->settings->payment_gateway,
            $request->all()
        );

        return [
            'text' => $customer->name,
            'value' => $customer->id
        ];

        // $user = User::where('email', $request->get('email'))->first();
        // $store = $this->store;
        // $storeId = $this->store->id;
        // $email = $request->get('email') ? $request->get('email') : 'noemail-' . substr(uniqid(rand(10, 99), false), 0, 12) . '@goprep.com';
        // $password = $request->get('password');

        // $existingUser = User::where('email', $email)->first();

        // if ($existingUser) {
        //     return response()->json(
        //         [
        //             'message' =>
        //                 'A user with this email address already exists. Please search the email in the customer box or add the email to your list of customers at the top.'
        //         ],
        //         400
        //     );
        // }

        // $existingPhoneUser = Customer::where('phone', $request->get('phone'))
        //     ->with('user')
        //     ->first();
        // $existingUserEmail = $existingPhoneUser
        //     ? $existingPhoneUser->user->email
        //     : null;

        // if ($existingPhoneUser) {
        //     return response()->json(
        //         [
        //             'message' =>
        //                 'A user with this phone number already exists. Please search the phone number in the customer box or add the email to your list of customers at the top: ' .
        //                 $existingUserEmail
        //         ],
        //         400
        //     );
        // }

        // if (!$user) {
        //     $user = User::create([
        //         'user_role_id' => 1,
        //         'email' => $email,
        //         'password' => $password
        //             ? bcrypt($password)
        //             : Hash::make(str_random(10)),
        //         'timezone' => 'America/New_York',
        //         'remember_token' => Hash::make(str_random(10)),
        //         'accepted_tos' => 1,
        //         'added_by_store_id' => $storeId,
        //         'referralUrlCode' =>
        //             'R' .
        //             strtoupper(substr(uniqid(rand(10, 99), false), -4)) .
        //             chr(rand(65, 90)) .
        //             rand(0, 9) .
        //             rand(0, 9) .
        //             chr(rand(65, 90))
        //     ]);

        //     $zip = $request->get('zip') ? $request->get('zip') : 'N/A';
        //     if ($store->details->country == 'US') {
        //         $zip = substr($zip, 0, 5);
        //     }

        //     $userDetails = $user->details()->create([
        //         'companyname' => $request->get('company_name'),
        //         'email' => $email,
        //         'firstname' => $request->get('firstname'),
        //         'lastname' => $request->get('lastname'),
        //         'phone' => $request->get('phone'),
        //         'address' => $request->get('address')
        //             ? $request->get('address')
        //             : 'N/A',
        //         'city' => $request->get('city') ? $request->get('city') : 'N/A',
        //         'state' => $request->get('state')
        //             ? $request->get('state')
        //             : null,
        //         'zip' => $zip,
        //         'delivery' => $request->get('delivery')
        //             ? $request->get('delivery')
        //             : 'N/A',
        //         'country' => $store->details->country,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //         'notifications' => array(
        //             'delivery_today' => true,
        //             'meal_plan' => true,
        //             'meal_plan_paused' => true,
        //             'new_order' => true,
        //             'subscription_meal_substituted' => true,
        //             'subscription_renewing' => true
        //         )
        //     ]);
        // } else {
        //     // Update user detail with newly entered data
        //     $userDetail = UserDetail::where('user_id', $user->id)->first();
        //     $userDetail->email = $email;
        //     $userDetail->firstname = $request->get('firstname');
        //     $userDetail->lastname = $request->get('lastname');
        //     $userDetail->address = $request->get('address')
        //         ? $request->get('address')
        //         : 'N/A';
        //     $userDetail->city = $request->get('city')
        //         ? $request->get('city')
        //         : 'N/A';
        //     $userDetail->zip = $request->get('zip')
        //         ? $request->get('zip')
        //         : 'N/A';
        //     $userDetail->state = $request->get('state')
        //         ? $request->get('state')
        //         : 'N/A';
        //     $userDetail->phone = $request->get('phone');
        //     $userDetail->country = 'USA';
        //     $userDetail->delivery = $request->get('delivery')
        //         ? $request->get('delivery')
        //         : 'N/A';
        //     $userDetail->update();
        // }

        // $user = User::findOrFail($user->id);
        // $user->createStoreCustomer(
        //     $storeId,
        //     $store->settings->currency,
        //     $store->settings->payment_gateway
        // );

        // $id = Customer::where('user_id', $user->id)
        //     ->pluck('id')
        //     ->first();

        // $firstname = UserDetail::where('user_id', $user->id)
        //     ->pluck('firstname')
        //     ->first();

        // $lastname = UserDetail::where('user_id', $user->id)
        //     ->pluck('lastname')
        //     ->first();

        // return [
        //     'text' => $firstname . ' ' . $lastname,
        //     'value' => $id
        // ];
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
