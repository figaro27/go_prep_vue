<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Store\StoreController;
use Illuminate\Http\Request;
use App\Customer;
use App\User;
use Illuminate\Support\Facades\Hash;
use Stripe;
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
        $password = $request->get('password');

        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            return response()->json(
                [
                    'message' =>
                        'A user with this email address already exists. Please search the email in the customer box or add the email to your list of customers at the top.'
                ],
                400
            );
        }

        $existingPhoneUser = UserDetail::where('phone', $request->get('phone'))
            ->with('user')
            ->first();
        $existingUserEmail = $existingPhoneUser
            ? $existingPhoneUser->user->email
            : null;

        if ($existingPhoneUser) {
            return response()->json(
                [
                    'message' =>
                        'A user with this phone number already exists. Please search the phone number in the customer box or add the email to your list of customers at the top: ' .
                        $existingUserEmail
                ],
                400
            );
        }

        if (!$user) {
            $user = User::create([
                'user_role_id' => 1,
                'email' => $email,
                'password' => $password
                    ? bcrypt($password)
                    : Hash::make(str_random(10)),
                'timezone' => 'America/New_York',
                'remember_token' => Hash::make(str_random(10)),
                'accepted_tos' => 1,
                'added_by_store_id' => $storeId,
                'referralUrlCode' =>
                    'R' .
                    strtoupper(substr(uniqid(rand(10, 99), false), -4)) .
                    chr(rand(65, 90)) .
                    rand(0, 9) .
                    rand(0, 9) .
                    chr(rand(65, 90))
            ]);

            $zip = $request->get('zip') ? $request->get('zip') : 'N/A';
            if ($store->details->country == 'US') {
                $zip = substr($zip, 0, 5);
            }

            $userDetails = $user->details()->create([
                'companyname' => $request->get('company_name'),
                'firstname' => $request->get('first_name'),
                'lastname' => $request->get('last_name'),
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
                    'subscription_renewing' => true
                )
            ]);
        } else {
            // Update user detail with newly entered data
            $userDetail = UserDetail::where('user_id', $user->id)->first();
            $userDetail->firstname = $request->get('first_name');
            $userDetail->lastname = $request->get('last_name');
            $userDetail->address = $request->get('address')
                ? $request->get('address')
                : 'N/A';
            $userDetail->city = $request->get('city')
                ? $request->get('city')
                : 'N/A';
            $userDetail->zip = $request->get('zip')
                ? $request->get('zip')
                : 'N/A';
            $userDetail->state = $request->get('state')
                ? $request->get('state')
                : 'N/A';
            $userDetail->phone = $request->get('phone');
            $userDetail->country = 'USA';
            $userDetail->delivery = $request->get('delivery')
                ? $request->get('delivery')
                : 'N/A';
            $userDetail->update();
        }

        $user = User::findOrFail($user->id);
        $user->createStoreCustomer(
            $storeId,
            $store->settings->currency,
            $store->settings->payment_gateway
        );

        $id = Customer::where('user_id', $user->id)
            ->pluck('id')
            ->first();

        $firstname = UserDetail::where('user_id', $user->id)
            ->pluck('firstname')
            ->first();

        $lastname = UserDetail::where('user_id', $user->id)
            ->pluck('lastname')
            ->first();

        return [
            'text' => $firstname . ' ' . $lastname,
            'value' => $id
        ];

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
