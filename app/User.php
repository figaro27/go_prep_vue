<?php

namespace App;

use App\Billing\Authorize;
use App\Billing\Constants;
use App\Customer;
use App\Mail\Customer\DeliveryToday;
use App\Mail\Customer\MealPlan;
use App\Mail\Customer\MealPLanPaused;
use App\Mail\Customer\NewOrder;
use App\Mail\Customer\SubscriptionRenewing;
use App\Mail\Customer\SubscriptionMealSubstituted;
use App\Mail\Customer\SubscriptionCancelled;
use App\Mail\Customer\AdjustedOrder;
use App\Mail\Customer\NewReferral;
use App\Mail\Customer\AbandonedCart;
use App\Mail\Customer\Survey;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Stripe;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Media\Utils as MediaUtils;
use App\Subscription;
use App\Referral;
use App\Order;
use App\Notifications\MailResetPasswordToken;
use App\StoreDetail;
use App\Store;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_role_id',
        'accepted_tos',
        'stripe_id',
        'added_by_store_id',
        'last_viewed_store_id',
        'referralUrlCode'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'stripe_id',
        'orders',
        'store',
        'last_viewed_store',
        'cards'
    ];

    protected $casts = [
        'stripe_account' => 'json'
    ];

    protected $appends = [
        'name',
        'cards',
        'last_viewed_store',
        'has_active_subscription',
        'active_subscription_id',
        'referrals',
        'orderCount',
        'last_viewed_store_url'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getId()
    {
        return $this->id;
    }

    public function userRole()
    {
        return $this->hasOne('App\UserRole');
    }

    public function userDetail()
    {
        return $this->hasOne('App\UserDetail');
    }

    public function details()
    {
        return $this->hasOne('App\UserDetail');
    }

    public function surveyResponses()
    {
        return $this->hasMany('App\SurveyResponse');
    }

    public function customers()
    {
        return $this->hasMany('App\Customer');
    }

    public function menuSessions()
    {
        return $this->hasMany('App\MenuSession');
    }

    public function order()
    {
        return $this->hasMany('App\Order');
    }

    public function orders()
    {
        return $this->hasMany('App\Order')->orderBy('created_at', 'desc');
    }

    public function subscriptions()
    {
        return $this->hasMany('App\Subscription');
    }

    public function cards()
    {
        return $this->hasMany('App\Card');
    }

    public function store()
    {
        return $this->hasOne('App\Store');
    }

    public function referrals()
    {
        return $this->hasMany('App\Referrals');
    }

    public function hasRole($role)
    {
        $roleMap = [
            'customer' => 1,
            'store' => 2,
            'admin' => 3
        ];

        return $this->user_role_id === $roleMap[$role];
    }

    public function getNameAttribute()
    {
        return $this->userDetail->full_name ?? '';
    }

    public function getCardsAttribute()
    {
        try {
            return \Stripe\Customer::retrieve($this->stripe_id)->sources->all([
                'object' => 'card'
            ]);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getLastViewedStoreAttribute()
    {
        return Store::find($this->last_viewed_store_id);
    }

    // Admin View

    public static function getCustomers()
    {
        return User::with('userDetail', 'order')
            ->where('user_role_id', '=', 1)
            ->get()
            ->map(function ($user) {
                return [
                    "id" => $user->id,
                    "user_role_id" => $user->user_role_id,
                    "Name" =>
                        $user->userDetail->firstname .
                        ' ' .
                        $user->userDetail->lastname,
                    "phone" => $user->userDetail->phone,
                    "address" => $user->userDetail->address,
                    "city" => $user->userDetail->city,
                    "state" => $user->userDetail->state,
                    "Joined" => $user->created_at->format('m-d-Y'),
                    "FirstOrder" => optional(
                        $user->order->min("created_at")
                    )->format('m-d-Y'),
                    "LastOrder" => optional(
                        $user->order->max("created_at")
                    )->format('m-d-Y'),
                    "TotalPayments" => $user->order->count(),
                    "TotalPaid" =>
                        '$' .
                        number_format($user->order->sum("amount"), 2, '.', ',')
                ];
            });
    }

    //Store View

    public static function getStoreCustomers()
    {
        $id = Auth::user()->id;
        $customers = Order::all()
            ->unique('user_id')
            ->where('store_id', $id)
            ->pluck('user_id');
        return User::with('userDetail', 'order')
            ->whereIn('id', $customers)
            ->get()
            ->map(function ($user) {
                return [
                    "id" => $user->id,
                    "name" =>
                        $user->userDetail->firstname .
                        ' ' .
                        $user->userDetail->lastname,
                    "Name" =>
                        $user->userDetail->firstname .
                        ' ' .
                        $user->userDetail->lastname,
                    "phone" => $user->userDetail->phone,
                    "address" => $user->userDetail->address,
                    "city" => $user->userDetail->city,
                    "state" => $user->userDetail->state,
                    "joined" => $user->created_at->format('m-d-Y'),
                    "Joined" => $user->created_at->format('m-d-Y'),
                    "last_order" => $user->order
                        ->max("created_at")
                        ->format('m-d-Y'),
                    "LastOrder" => $user->order
                        ->max("created_at")
                        ->format('m-d-Y'),
                    "total_payments" => $user->order->count(),
                    "TotalPayments" => $user->order->count(),
                    "total_paid" => $user->order->sum("amount"),
                    "TotalPaid" => $user->order->sum("amount")
                ];
            });
    }

    /**
     * Undocumented function
     *
     * @param array[Store] $stores
     * @return float
     */
    public function distanceFrom($stores)
    {
        if (is_object($stores)) {
            $stores = [$stores];
        }

        if (!is_array($stores)) {
            return null;
        }

        $origins = [];
        foreach ($stores as $store) {
            $origin = $store->details;
            $origins[] = implode(',', [
                $origin->address,
                $origin->city,
                $origin->state,
                $origin->zip,
                $origin->country
            ]);
        }
        $dest = $this->userDetail;

        $query = [
            'origins' => implode('|', $origins),
            'destinations' => implode(',', [
                $dest->address,
                $dest->city,
                $dest->state,
                $dest->zip
            ]),
            'key' => config('google.api_key')
        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET',
            'https://maps.googleapis.com/maps/api/distancematrix/json',
            [
                'query' => $query
            ]
        );
        $status = $res->getStatusCode();
        // "200"
        $body = json_decode((string) $res->getBody());

        try {
            if (count($body->rows) > 0) {
                if (isset($body->rows[0]->elements[0]->distance)) {
                    $distance = $body->rows[0]->elements[0]->distance->value;
                    return $distance * 0.000621371;
                }
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    public function hasStoreCustomer(
        $storeId,
        $currency = 'USD',
        $gateway = Constants::GATEWAY_STRIPE
    ) {
        $customer = Customer::where([
            'user_id' => $this->id,
            'store_id' => $storeId,
            'currency' => $currency,
            'payment_gateway' => $gateway
        ])->first();
        return !is_null($customer);
    }

    public function getStoreCustomer(
        $storeId,
        $currency = 'USD',
        $gateway = Constants::GATEWAY_STRIPE,
        $fromGateway = false
    ) {
        $store = Store::find($storeId);
        $customer = Customer::where([
            'user_id' => $this->id,
            'store_id' => $storeId,
            'currency' => $currency,
            'payment_gateway' => $gateway
        ])->first();

        if (!$fromGateway) {
            return $customer;
        } elseif ($gateway === Constants::GATEWAY_STRIPE) {
            $acct = $store->settings->stripe_account;
            \Stripe\Stripe::setApiKey($acct['access_token']);
            $stripeCustomer = \Stripe\Customer::retrieve($customer->stripe_id);
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            return $stripeCustomer;
        } elseif ($gateway === Constants::GATEWAY_AUTHORIZE) {
        }

        return null;
    }

    public function createStoreCustomer(
        $storeId,
        $currency = 'USD',
        $gateway = Constants::GATEWAY_STRIPE,
        $existingUserNewCustomer = null
    ) {
        $store = Store::find($storeId);

        if ($existingUserNewCustomer) {
            $name =
                $existingUserNewCustomer['firstname'] .
                ' ' .
                $existingUserNewCustomer['lastname'];
            $firstname = $existingUserNewCustomer['firstname'];
            $lastname = $existingUserNewCustomer['lastname'];
            $phone = $existingUserNewCustomer['phone'];
            $address = isset($existingUserNewCustomer['address'])
                ? $existingUserNewCustomer['address']
                : 'N/A';
            $city = isset($existingUserNewCustomer['city'])
                ? $existingUserNewCustomer['city']
                : 'N/A';
            $state = isset($existingUserNewCustomer['state'])
                ? $existingUserNewCustomer['state']
                : null;
            $zip = isset($existingUserNewCustomer['zip'])
                ? $existingUserNewCustomer['zip']
                : 'N/A';
            $delivery = isset($existingUserNewCustomer['delivery'])
                ? $existingUserNewCustomer['delivery']
                : 'N/A';
            $company = isset($existingUserNewCustomer['company'])
                ? $existingUserNewCustomer['company']
                : 'N/A';
        }

        if (
            $existingUserNewCustomer &&
            !isset($existingUserNewCustomer['state'])
        ) {
            $state = null;
        } else {
            $state = $this->userDetail ? $this->userDetail->state : null;
        }

        $customer = new Customer();
        $customer->user_id = $this->id;
        $customer->store_id = $storeId;
        $customer->currency = $currency;
        $customer->payment_gateway = $gateway;
        $customer->name = isset($name)
            ? $name
            : $this->userDetail->firstname . ' ' . $this->userDetail->lastname;
        $customer->firstname = isset($firstname)
            ? $firstname
            : $this->userDetail->firstname;
        $customer->lastname = isset($lastname)
            ? $lastname
            : $this->userDetail->lastname;
        $customer->phone = isset($phone) ? $phone : $this->userDetail->phone;
        $customer->address = isset($address)
            ? $address
            : $this->userDetail->address;
        $customer->city = isset($city) ? $city : $this->userDetail->city;
        $customer->state = $state;
        $customer->zip = isset($zip) ? $zip : $this->userDetail->zip;
        $customer->delivery = isset($delivery)
            ? $delivery
            : $this->userDetail->delivery;
        $customer->company = (isset($company)
                ? $company
                : $this->userDetail->companyname)
            ? $this->userDetail->companyname
            : 'N/A';
        $customer->email = $this->email;
        $customer->total_payments = 0;
        $customer->total_paid = 0;

        if ($gateway === Constants::GATEWAY_STRIPE) {
            $acct = $store->settings->stripe_account;
            \Stripe\Stripe::setApiKey($acct['access_token']);
            $stripeCustomer = \Stripe\Customer::create([
                'email' => $this->email,
                'description' => isset($name) ? $name : $this->name
            ]);
            $gatewayCustomerId = $stripeCustomer->id;
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $customer->stripe_id = $stripeCustomer->id;
        } elseif ($gateway === Constants::GATEWAY_AUTHORIZE) {
            $authorize = new Authorize($store);
            $gatewayCustomerId = $authorize->createCustomer($this);
            $customer->stripe_id = $gatewayCustomerId;
        }

        $customer->save();

        return $customer;
    }

    public function createCustomer($token, $gateway = Constants::GATEWAY_STRIPE)
    {
        $stripeCustomer = \Stripe\Customer::create([
            "source" => $token
        ]);

        $this->stripe_id = $stripeCustomer->id;
        $this->save();

        return $stripeCustomer;
    }

    public function hasCustomer($gateway = Constants::GATEWAY_STRIPE)
    {
        if ($gateway === Constants::GATEWAY_STRIPE) {
            return !!$this->stripe_id;
        } elseif ($gateway === Constants::GATEWAY_AUTHORIZE) {
            // can't share users between merchants
        } else {
            return false;
        }
    }

    public function createCard($token)
    {
        $customer = \Stripe\Customer::retrieve($this->stripe_id);

        return $customer->sources->create(["source" => $token]);
    }

    public function notificationEnabled($notif)
    {
        if (!$this->details) {
            return false;
        }
        return $this->details->notificationEnabled($notif);
    }

    public function sendNotification($notif, $data = [])
    {
        $store = $email = null;

        if (!isset($data['user'])) {
            $data['user'] = $this;
        }

        if (isset($data['store']) && $data['store']) {
            $store = $data['store'];
        } elseif (isset($data['order']) && $data['order']) {
            $order = $data['order'];
            $store = $order->store;
        } elseif (isset($data['subscription']) && $data['subscription']) {
            $sub = $data['subscription'];
            $store = $sub->store;
        }

        if ($store) {
            $storeDetails = $store->details;
            $settings = $store->settings;

            if ($settings && $settings->timezone) {
                $timezone = $settings->timezone;
                date_default_timezone_set($timezone);
            }

            /* Check Email Branding */
            if (
                isset($store->modules) &&
                isset($store->modules->emailBranding)
            ) {
                $emailBranding = (int) $store->modules->emailBranding;

                if ($emailBranding == 1) {
                    $logo = $storeDetails->getMedia('logo')->first();

                    if ($logo) {
                        $path = $logo->getPath('thumb');

                        if (file_exists($path)) {
                            $logo_b64 = \App\Utils\Images::encodeB64($path);

                            if ($logo_b64) {
                                $data['logo_b64'] = $logo_b64;
                            }
                        }
                    }
                }
            }
            /* Check Email Branding End */
        }

        $bcc = false;
        $receipt = isset($data['receipt']) ? $data['receipt'] : false;

        switch ($notif) {
            case 'abandoned_cart':
                $email = new AbandonedCart($data);
                break;
            case 'delivery_today':
                $email = new DeliveryToday($data);
                break;
            case 'meal_plan':
                $email = new MealPlan($data);
                $bcc = true;
                break;
            case 'meal_plan_paused':
                $email = new MealPlanPaused($data);
                break;
            case 'new_order':
                $email = new NewOrder($data);
                $bcc = true;
                break;
            case 'subscription_renewing':
                $email = new SubscriptionRenewing($data);
                break;
            case 'subscription_meal_substituted':
                $email = new SubscriptionMealSubstituted($data);
                break;
            case 'subscription_cancelled':
                $email = new SubscriptionCancelled($data);
                break;
            case 'adjusted_order':
                $email = new AdjustedOrder($data);
                break;
            case 'new_referral':
                $email = new NewReferral($data);
                break;
            case 'survey':
                $email = new Survey($data);
                break;
        }

        if ($email && strpos($this->email, 'noemail') === false) {
            try {
                if ($bcc === true && $receipt === false) {
                    Mail::to($this)
                        ->bcc('orders@goprep.com')
                        ->send($email);
                    return true;
                } else {
                    Mail::to($this)->send($email);
                    return true;
                }
            } catch (\Exception $e) {
            }
        }

        return false;
    }

    public function getHasActiveSubscriptionAttribute()
    {
        $subscriptions = Subscription::where([
            'user_id' => $this->id,
            'status' => 'active'
        ])->count();
        if ($subscriptions > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getActiveSubscriptionIdAttribute()
    {
        $subId = Subscription::where('user_id', $this->id)
            ->where('status', '!=', 'cancelled')
            ->pluck('id')
            ->first();
        return $subId;
    }

    public function getReferralsAttribute()
    {
        return Referral::where('user_id', $this->id)->get();
    }

    public function getorderCountAttribute()
    {
        return Order::where([
            'user_id' => $this->id,
            'store_id' => $this->last_viewed_store_id,
            'paid' => 1
        ])->count();
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MailResetPasswordToken($token));
    }

    public function getLastViewedStoreUrlAttribute()
    {
        if ($this->user_role_id === 1 && $this->last_viewed_store_id) {
            $storeDetail = StoreDetail::where(
                'id',
                $this->last_viewed_store_id
            )->first();
            if ($storeDetail->host) {
                $host = $storeDetail->host ? $storeDetail->host : 'goprep';
                $start =
                    env('APP_ENV') == 'production' ? 'https://' : 'http://';
                $end =
                    env('APP_ENV') == 'production' ? '.com' : '.localhost:8000';
                return $start . $storeDetail->domain . '.' . $host;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
