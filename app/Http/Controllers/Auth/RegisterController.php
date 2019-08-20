<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Store;
use App\User;
use App\StorePlan;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\Store\Onboarding;
use Carbon\Carbon;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
     */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    protected $regex = [
        'domain' => '/^[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])?$/is'
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $v = Validator::make($data, [
            //'name' => 'required|string|max:255',
            'user.role' => 'required|in:customer,store',
            'user.email' => 'required|string|email|max:255|unique:users,email',
            'user.password' => 'required|string|min:6|confirmed',
            'user.first_name' => 'required',
            'user.last_name' => 'required',
            'user.phone' => 'required',
            // 'user.accepted_tos' => 'in:1',

            'user_details.address' => 'required',
            'user_details.city' => 'required',
            'user_details.state' => 'required',
            'user_details.zip' => 'required|min:5'
        ]);

        $v->sometimes(
            'store',
            [
                'store.store_name' => ['required', 'unique:store_details,name'],
                'store.domain' => [
                    'required',
                    'unique:store_details,domain',
                    'regex:' . $this->regex['domain']
                ],
                'store.address' => 'required',
                'store.city' => 'required',
                'store.state' => 'required',
                'store.zip' => 'required'
            ],
            function ($data) {
                return $data['user']['role'] === 'store';
            }
        );

        return $v;
    }

    public function validateStep(Request $request, $step)
    {
        switch ($step) {
            case '0':
                $v = Validator::make($request->all(), [
                    'role' => 'required|in:customer,store',
                    'email' =>
                        'required|string|email|max:255|unique:users,email',
                    'password' => 'required|string|min:6|confirmed',
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'phone' => 'required'
                ]);
                break;

            case '1':
                $v = Validator::make($request->all(), [
                    'address' => 'required',
                    'city' => 'required',
                    'state' => 'required',
                    'zip' => 'required',
                    'accepted_tos' => 'in:1'
                ]);
                break;

            case '2':
                $v = Validator::make($request->all(), [
                    'store_name' => ['required', 'unique:store_details,name'],
                    'domain' => [
                        'required',
                        'unique:store_details,domain',
                        'regex:' . $this->regex['domain']
                    ],
                    //'currency' => ['required', 'in:USD,GBP,CAD'],
                    'address' => 'required',
                    'city' => 'required',
                    'state' => 'required',
                    'zip' => 'required',
                    'accepted_tos' => 'in:1'
                    //'accepted_toa' => 'in:1'
                ]);
                break;

            case '3':
                $v = Validator::make($request->all(), [
                    'plan' => 'required'
                ]);
        }

        return $v->validate();
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            //'name' => $data['name'],
            'user_role_id' => $data['user']['role'] === 'store' ? 2 : 1,
            'email' => $data['user']['email'],
            'password' => Hash::make($data['user']['password']),
            'timezone' => 'America/New_York',
            'remember_token' => Hash::make(str_random(10)),
            'accepted_tos' => 1
        ]);

        $userDetails = $user->details()->create([
            //'user_id' => $user->id,
            'firstname' => $data['user']['first_name'],
            'lastname' => $data['user']['last_name'],
            'phone' => $data['user']['phone'],
            'address' => $data['user_details']['address'],
            'city' => $data['user_details']['city'],
            'state' => $data['user_details']['state'],
            'zip' => $data['user_details']['zip'],
            'country' => $data['user_details']['country'],
            'delivery' => isset($data['user_details']['delivery'])
                ? $data['user_details']['delivery']
                : '',
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

        if ($data['user']['role'] === 'store') {
            $store = $user->store()->create([
                //'accepted_toa' => 1
            ]);

            $storeDetail = $store->details()->create([
                'name' => $data['store']['store_name'],
                'phone' => $data['user']['phone'],
                'address' => $data['store']['address'],
                'city' => $data['store']['city'],
                'state' => $data['store']['state'],
                'zip' => $data['store']['zip'],
                'country' => $data['store']['country'],
                'logo' => '',
                'domain' => $data['store']['domain'],
                'created_at' => now()
            ]);

            switch ($data['store']['country']) {
                case 'GB':
                    $timezone = 'Europe/London';
                    break;
                default:
                    $timezone = 'America/New_York';
            }

            if (
                isset($data['store']['currency']) &&
                in_array($data['store']['currency'], ['USD', 'GBP', 'CAD'])
            ) {
                $currency = $data['store']['currency'];
            } else {
                switch ($data['store']['country']) {
                    case 'GB':
                        $currency = 'GBP';
                        break;
                    case 'CA':
                        $currency = 'CAD';
                        break;
                    default:
                        $currency = 'USD';
                }
            }

            $storeSettings = $store->settings()->create([
                'timezone' => $timezone,
                'currency' => $currency,
                'open' => 0,
                'notifications' => [],
                'transferType' => 'delivery',
                'view_delivery_days' => 1,
                'delivery_days' => [],
                'delivery_distance_zipcodes' => [],
                'notifications' => array(
                    'new_order' => true,
                    'new_orders' => true,
                    'ready_to_print' => true,
                    'new_subscription' => true,
                    'new_subscriptions' => true,
                    'cancelled_subscription' => true,
                    'cancelled_subscriptions' => true
                )
            ]);

            $storeSettings = $store->categories()->create([
                'category' => 'Entrees'
            ]);

            try {
                $key = new \Cloudflare\API\Auth\APIKey(
                    config('services.cloudflare.user'),
                    config('services.cloudflare.key')
                );
                $adapter = new \Cloudflare\API\Adapter\Guzzle($key);
                $zones = new \Cloudflare\API\Endpoints\Zones($adapter);
                $dns = new \Cloudflare\API\Endpoints\DNS($adapter);

                $zoneId = $zones->getZoneID(config('services.cloudflare.zone'));

                $dns->addRecord(
                    $zoneId,
                    'CNAME',
                    $storeDetail->domain . '.dev',
                    config('services.cloudflare.zone'),
                    0,
                    true
                );
                $dns->addRecord(
                    $zoneId,
                    'CNAME',
                    $storeDetail->domain,
                    config('services.cloudflare.zone'),
                    0,
                    true
                );
            } catch (\Exception $e) {
                // todo: send notification to admin
            }

            // Create plan
            $plans = config('plans');
            $planObj = collect($data['plan']);
            $planId = $planObj->get('plan');
            $planMethod = $planObj->get('plan_method');
            $planPeriod = $planObj->get('plan_period');
            $planToken = $planObj->get('stripe_token');

            try {
                $plan = collect($plans[$planId][$planPeriod]);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }

            $storePlan = new StorePlan();
            $storePlan->active = 1;
            $storePlan->store_id = $store->id;
            $storePlan->method = $planMethod;
            $storePlan->amount = $plan->get('price');
            $storePlan->period = $planPeriod;
            $storePlan->day = date('d');

            // If using credit card billing, charge here
            if ($planMethod === 'credit_card') {
                // Create customer
                $customer = \Stripe\Customer::create([
                    'description' => '',
                    'source' => $planToken
                ]);

                $subscription = \Stripe\Subscription::create([
                    'customer' => $customer,
                    'items' => [
                        [
                            'plan' => $plan->get('stripe_id')
                        ]
                    ]
                ]);

                $storePlan->stripe_customer_id = $customer->id;
                $storePlan->stripe_subscription_id = $subscription->id;
            }

            $storePlan->save();
        }

        return $user;
    }

    protected function registered(Request $request, $user)
    {
        // Create auth token
        $token = auth()->login($user);

        // Determine redirect URL
        if ($user->hasRole('store')) {
            $email = new Onboarding([
                'user' => $user ?? null
            ]);
            try {
                Mail::to($user)
                    ->bcc('mike@goprep.com')
                    ->send($email);
            } catch (\Exception $e) {
            }
            if ($request->has('plan_id')) {
                $plans = collect(config('app.store_plans'));

                $plan = $plans->get($request->get('plan_id'));

                if ($plan) {
                    $tom = Carbon::tomorrow();
                    $dom = $tom->day;
                    $dow = $tom->dayOfWeekIso;

                    StorePlan::create(
                        $user->store,
                        $plan['value'],
                        $plan['period'],
                        $plan['period'] === 'week' ? $dow : $dom
                    );
                }
            }
            $redirect = $user->store->getConnectUrl(); //'/store/account/settings';
        } else {
            $store = defined('STORE_ID') ? Store::find(STORE_ID) : null;
            $redirect = $store ? '/customer/menu' : '/customer/home';
        }

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' =>
                auth()
                    ->factory()
                    ->getTTL() * 60,
            'redirect' => $redirect
        ];
    }
}
