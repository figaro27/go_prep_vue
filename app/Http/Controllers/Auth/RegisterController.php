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
            'remember_token' => Hash::make(str_random(10)),
            'accepted_tos' => 1,
            'referralUrlCode' =>
                'R' .
                strtoupper(substr(uniqid(rand(10, 99), false), -4)) .
                chr(rand(65, 90)) .
                rand(0, 9) .
                rand(0, 9) .
                chr(rand(65, 90))
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
            'firstname' => $data['user']['first_name'],
            'lastname' => $data['user']['last_name'],
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
                'delivery_days' => [],
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
                )
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

            $planless = $data['planless'] ?? false;

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

                $upfrontFee = $plan->get('price_upfront', null);

                if (!$payAsYouGo) {
                    $storePlan = new StorePlan();
                    $storePlan->active = 1;
                    $storePlan->store_id = $store->id;
                    $storePlan->method = $planMethod;
                    $storePlan->amount = $plan->get('price');
                    $storePlan->period = $planPeriod;
                    $storePlan->day = date('d');
                }

                // A credit card was entered
                if ($planToken) {
                    // Create customer
                    $customer = \Stripe\Customer::create([
                        'description' => '',
                        'source' => $planToken
                    ]);
                }

                // If using credit card billing, charge here
                if (!$payAsYouGo && $planMethod === 'credit_card') {
                    $subscription = \Stripe\Subscription::create([
                        'customer' => $customer,
                        'trial_from_plan' =>
                            $data['plan']['plan'] == 'free_trial'
                                ? true
                                : false,
                        'items' => [
                            [
                                'plan' => $plan->get('stripe_id')
                            ]
                        ]
                    ]);

                    $storePlan->stripe_customer_id = $customer->id;
                    $storePlan->stripe_subscription_id = $subscription->id;
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

                if (!$payAsYouGo) {
                    $storePlan->save();
                }
            }
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
