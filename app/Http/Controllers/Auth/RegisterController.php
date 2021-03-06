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
use App\UserDetail;
use Illuminate\Support\Facades\Log;
use App\SmsList;

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
            'user.role' => 'required|in:customer,store,guest',
            'user.email' => 'required|string|email|max:255|unique:users,email',
            'user.password' => 'required|string|min:6|confirmed',
            'user.firstname' => 'required',
            'user.lastname' => 'required',
            'user.phone' => 'required',
            // 'user.accepted_tos' => 'in:1',

            'user_details.address' => 'required',
            'user_details.city' => 'required',
            'user_details.zip' => 'required'
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
        $existingUser = UserDetail::where('phone', $request->get('phone'))
            ->with('user')
            ->first();
        if ($existingUser) {
            $existingUserEmail = preg_replace(
                "/(?!^).(?=[^@]+@)/",
                "*",
                $existingUser->user->email
            );
        } else {
            $existingUserEmail = null;
        }

        switch ($step) {
            case '0':
                if (!$request->get('guest')) {
                    $v = Validator::make(
                        $request->all(),
                        [
                            'role' => 'required|in:customer,store,guest',
                            'email' =>
                                'required|string|email|max:255|unique:users,email',
                            'password' => 'required|string|min:6|confirmed',
                            'firstname' => 'required',
                            'lastname' => 'required',
                            'phone' => 'required|unique:user_details,phone'
                        ],
                        [
                            'phone.unique' =>
                                'An account using this phone number already exists. Email: ' .
                                $existingUserEmail
                        ]
                    );
                } else {
                    // Not requiring a unique phone number on guest signup
                    $v = Validator::make($request->all(), [
                        'role' => 'required|in:customer,store,guest',
                        'email' =>
                            'required|string|email|max:255|unique:users,email',
                        'password' => 'required|string|min:6|confirmed',
                        'firstname' => 'required',
                        'lastname' => 'required',
                        'phone' => 'required'
                    ]);
                }
                break;

            case '1':
                $v = Validator::make($request->all(), [
                    'address' => 'required',
                    'city' => 'required',
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
        try {
            switch ($data['user']['role']) {
                case 'store':
                    $role = 2;
                    break;
                case 'customer':
                    $role = 1;
                    break;
                case 'guest':
                    $role = 4;
                    break;
            }
            $user = User::create([
                //'name' => $data['name'],
                'user_role_id' => $role,
                'email' => $data['user']['email'],
                'password' => Hash::make($data['user']['password']),
                'remember_token' => Hash::make(str_random(10)),
                'accepted_tos' => 1,
                'referralUrlCode' =>
                    'R' .
                    strtoupper(substr(uniqid(rand(10, 99), false), -4)) .
                    chr(rand(65, 90)) .
                    rand(0, 9) .
                    rand(0, 9) .
                    chr(rand(65, 90)),
                'last_viewed_store_id' => $data['last_viewed_store_id'] ?? null
            ]);

            $zip = $data['user_details']['zip']
                ? $data['user_details']['zip']
                : 'N/A';
            if ($data['user_details']['country'] == 'US') {
                $zip = substr($zip, 0, 5);
            }

            $userDetails = $user->details()->create([
                //'user_id' => $user->id,
                'companyname' => isset($data['user']['company_name'])
                    ? $data['user']['company_name']
                    : null,
                'email' => $data['user']['email'],
                'firstname' => $data['user']['firstname'],
                'lastname' => $data['user']['lastname'],
                'phone' => $data['user']['phone'],
                'address' => isset($data['user_details']['unit'])
                    ? $data['user_details']['address'] .
                        ' ' .
                        $data['user_details']['unit']
                    : $data['user_details']['address'],
                'city' => $data['user_details']['city'],
                'state' => $data['user_details']['state'],
                'zip' => $zip,
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
                    'subscription_renewing' => true,
                    'new_referral' => true
                ),
                'store_id' => $data['last_viewed_store_id'] ?? null
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
                    'timezone' => $this->getTimeZone($data),
                    'currency' => $currency,
                    'open' => 0,
                    'notifications' => [],
                    'transferType' => 'delivery',
                    'view_delivery_days' => 1,
                    'delivery_days' => ["sun"],
                    'delivery_distance_zipcodes' => [],
                    'meal_packages' => 1,
                    'notifications' => array(
                        'new_order' => true,
                        'new_orders' => true,
                        'ready_to_print' => true,
                        'new_subscription' => true,
                        'new_subscriptions' => true,
                        'cancelled_subscription' => true,
                        'cancelled_subscriptions' => true
                    ),
                    'subscriptionRenewalType' => 'cutoff',
                    'application_fee' =>
                        collect($data['plan'])->get('plan') === 'pay-as-you-go'
                            ? 5
                            : 0
                ]);

                $storeSettings = $store->categories()->create([
                    'category' => 'Entrees'
                ]);

                $storeModules = $store->modules()->create([
                    'cashOrders' => 1,
                    'manualOrders' => 1,
                    'manualCustomers' => 1,
                    'deposits' => 1,
                    'lineItems' => 1,
                    'orderNotes' => 1,
                    'emailBranding' => 1
                ]);

                $storeModuleSettings = $store->moduleSettings()->create();

                $storeReferralSettings = $store->referralSettings()->create([
                    'signupEmail' => 0,
                    'showInNotifications' => 0,
                    'showInMenu' => 0,
                    'type' => 'percent',
                    'amount' => 5.0
                ]);

                $storeReportSettings = $store->reportSettings()->create();
                $storeLabelSettings = $store->labelSettings()->create();
                $storeOrderLabelSettings = $store
                    ->orderLabelSettings()
                    ->create();
                $storeSMSSettings = $store->smsSettings()->create([
                    'autoSendOrderReminderTemplate' =>
                        'Last chance to order for {next delivery}. Our cutoff time is {cutoff}. Please order at {URL}.',
                    'autoSendDeliveryTemplate' =>
                        'Your order from {store name} {pickup/delivery} today.',
                    'autoSendOrderConfirmationTemplate' =>
                        'Thank you for your order. Your order {pickup/delivery} on {delivery date}.',
                    'autoSendSubscriptionRenewalTemplate' =>
                        'Your subscription from {store name} will renew in 24 hours. If you\'d like to make any changes, please visit {URL}.'
                ]);

                $storeSMSMasterList = $store->smsSettings->createMasterList();

                $storeReportRecords = $store->reportRecords()->create();

                $storeMenuSettings = $store->menuSettings()->create();

                $storePackingSlipSettings = $store
                    ->packingSlipSettings()
                    ->create();

                try {
                    $key = new \Cloudflare\API\Auth\APIKey(
                        config('services.cloudflare.user'),
                        config('services.cloudflare.key')
                    );
                    $adapter = new \Cloudflare\API\Adapter\Guzzle($key);
                    $zones = new \Cloudflare\API\Endpoints\Zones($adapter);
                    $dns = new \Cloudflare\API\Endpoints\DNS($adapter);

                    $zoneId = $zones->getZoneID(
                        config('services.cloudflare.zone')
                    );

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

                $planless = $data['planless'] ?? false;
                $freeTrial =
                    $data['plan']['plan'] == 'free_trial' ? true : false;

                if ($planless) {
                    // todo: send notification to admin
                } else {
                    // Create plan
                    $plans = config('plans');
                    $planObj = collect($data['plan']);
                    $planId = $planObj->get('plan');
                    $planMethod = $planObj->get('plan_method');
                    $planPeriod = $planObj->get('plan_period');
                    $planToken = $planObj->get('stripe_token');
                    $payAsYouGo = $planId === 'pay-as-you-go';
                    try {
                        $plan = collect($plans[$planId][$planPeriod]);
                    } catch (\Exception $e) {
                        Log::error($e->getMessage());
                    }

                    $allowed_orders =
                        $data['plan']['allowed_orders'] !== null
                            ? $data['plan']['allowed_orders']
                            : $plan->get('orders');

                    if ($allowed_orders == null) {
                        $allowed_orders = 50;
                    }

                    $upfrontFee = $plan->get('price_upfront', null);

                    if (!$payAsYouGo) {
                        $storePlan = new StorePlan();
                        $storePlan->status = 'active';
                        $storePlan->store_id = $store->id;
                        $storePlan->store_name = $store->details->name;
                        $storePlan->contact_email = $user->email;
                        $storePlan->contact_phone = $userDetails->phone;
                        $storePlan->contact_name =
                            $userDetails->firstname .
                            ' ' .
                            $userDetails->lastname;
                        $storePlan->method = $planMethod;
                        $storePlan->amount = $plan->get('price');
                        $storePlan->plan_name =
                            $planObj->get('plan') === 'free_trial'
                                ? 'basic'
                                : $planObj->get('plan');
                        $storePlan->allowed_orders = $allowed_orders;
                        $storePlan->period = $planPeriod;
                        $storePlan->free_trial = $freeTrial;
                        $storePlan->day = $freeTrial
                            ? Carbon::now()->addWeeks(2)->day
                            : Carbon::now()->day;
                        $storePlan->month = $freeTrial
                            ? Carbon::now()->addWeeks(2)->month
                            : Carbon::now()->month;
                    } else {
                        $storePlan = new StorePlan();
                        $storePlan->status = 'active';
                        $storePlan->store_id = $store->id;
                        $storePlan->store_name = $store->details->name;
                        $storePlan->contact_email = $user->email;
                        $storePlan->contact_phone = $userDetails->phone;
                        $storePlan->contact_name =
                            $userDetails->firstname .
                            ' ' .
                            $userDetails->lastname;
                        $storePlan->method = 'n/a';
                        $storePlan->amount = 0;
                        $storePlan->plan_name = 'pay-as-you-go';
                        $storePlan->allowed_orders = 0;
                        $storePlan->period = 'n/a';
                        $storePlan->free_trial = $freeTrial;
                        $storePlan->day = 0;
                        $storePlan->month = 0;
                    }

                    // A credit card was entered
                    if ($planToken) {
                        // Create customer
                        $customer = \Stripe\Customer::create([
                            'description' => '',
                            'source' => $planToken,
                            'email' => $user->email,
                            'name' =>
                                $userDetails->firstname .
                                ' ' .
                                $userDetails->lastname
                        ]);
                    }
                    if (isset($customer) && $customer && $customer->id) {
                        $storePlan->stripe_customer_id = $customer->id;
                    }

                    $storePlan->save();

                    if (isset($customer) && $customer && $customer->id) {
                        // If using credit card billing, charge here
                        if (!$payAsYouGo && $planMethod === 'credit_card') {
                            $subscription = \Stripe\Subscription::create([
                                'customer' => $customer,
                                'trial_from_plan' => $freeTrial,
                                'items' => [
                                    [
                                        'plan' => $plan->get('stripe_id')
                                    ]
                                ]
                            ]);

                            $storePlan->stripe_subscription_id =
                                $subscription->id;

                            // Get card ID
                            $customer = \Stripe\Customer::retrieve(
                                $customer->id,
                                []
                            );
                            $storePlan->stripe_card_id = $customer->allSources(
                                $customer->id,
                                []
                            )->data[0]['id'];
                        }

                        // Charge the up-front fee
                        if ($upfrontFee) {
                            $charge = \Stripe\Charge::create([
                                'amount' => $upfrontFee,
                                'currency' => 'usd',
                                'customer' => $customer,
                                'description' => 'GoPrep: One-time signup fee'
                            ]);
                        }

                        // if (!$payAsYouGo) {
                        $storePlan->update();
                    }
                    // }
                }
            }

            return $user;
        } catch (\Exception $e) {
            Log::channel('failed_registration')->info($e);

            if ($user) {
                $user->delete();
            }
            if ($userDetails) {
                $userDetails->delete();
            }
            if ($storeDetail) {
                $storeDetail->delete();
            }
            if ($storeSettings) {
                $storeSettings->delete();
            }
            if ($storeModules) {
                $storeModules->delete();
            }
            if ($storeModuleSettings) {
                $storeModuleSettings->delete();
            }
            if ($storeReferralSettings) {
                $storeReferralSettings->delete();
            }
            if ($storeReportSettings) {
                $storeReportSettings->delete();
            }
            if ($storeSMSSettings) {
                $storeSMSSettings->delete();
            }
            if ($storeReportRecords) {
                $storeReportRecords->delete();
            }
            if ($storeMenuSettings) {
                $storeMenuSettings->delete();
            }
            if ($storePackingSlipSettings) {
                $storePackingSlipSettings->delete();
            }
            if ($storePlan) {
                $storePlan->delete();
            }
            if ($storeLabelSettings) {
                $storeLabelSettings->delete();
            }
            if ($storeOrderLabelSettings) {
                $storeOrderLabelSettings->delete();
            }
            if ($storeSMSMasterList) {
                $smsList = SmsList::where('store_id', $store->id)->first();
                $smsList->delete();
            }
            if ($store) {
                $store->delete();
            }
        }
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
            $redirect =
                $request['plan']['plan'] === 'free_trial'
                    ? '/store/account/settings'
                    : $user->store->getConnectUrl();
        } else {
            $store = isset($user->last_viewed_store_id)
                ? $user->last_viewed_store_id
                : null;
            if (!$store) {
                $store = defined('STORE_ID') ? Store::find(STORE_ID) : null;
            }
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

    public function getTimeZone($data)
    {
        if ($data['store']['country'] === 'GB') {
            return 'Europe/London';
        }

        if ($data['store']['country'] === 'CA') {
            return 'Canada/Mountain';
        }

        switch ($data['store']['state']) {
            case 'NY':
            case 'NJ':
            case 'CT':
            case 'TN':
            case 'KY':
            case 'DE':
            case 'DC':
            case 'GA':
            case 'NC':
            case 'ME':
            case 'MD':
            case 'MA':
            case 'NH':
            case 'OH':
            case 'PA':
            case 'RI':
            case 'SC':
            case 'VT':
            case 'VA':
            case 'WV':
            case 'MI':
            case 'FL':
            case 'IN':
                return 'America/New_York';
                break;
            case 'AL':
            case 'AR':
            case 'IL':
            case 'IA':
            case 'LA':
            case 'MN':
            case 'MS':
            case 'MO':
            case 'OK':
            case 'WI':
            case 'KS':
            case 'TX':
                return 'America/Chicago';
                break;
            case 'ID':
            case 'AZ':
            case 'CO':
            case 'MT':
            case 'NM':
            case 'UT':
            case 'WY':
            case 'ND':
            case 'NE':
            case 'SD':
                return 'America/Denver';
                break;
            case 'CA':
            case 'WA':
            case 'OR':
            case 'NV':
                return 'America/Los_Angeles';
                break;
            case 'AK':
                return 'America/Anchorage';
                break;
            case 'HI':
                return 'America/Adak';
                break;
            default:
                return 'America/New_York';
        }
    }
}
