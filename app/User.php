<?php

namespace App;

use App\Billing\Constants;
use App\Customer;
use App\Mail\Customer\DeliveryToday;
use App\Mail\Customer\MealPlan;
use App\Mail\Customer\MealPLanPaused;
use App\Mail\Customer\NewOrder;
use App\Mail\Customer\SubscriptionRenewing;
use App\Mail\Customer\SubscriptionMealSubstituted;
use App\Mail\Customer\SubscriptionCancelled;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Stripe;
use Tymon\JWTAuth\Contracts\JWTSubject;

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
        'stripe_id'
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

    protected $appends = ['name', 'cards', 'last_viewed_store'];

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

    public function customers()
    {
        return $this->hasMany('App\Customer');
    }

    public function order()
    {
        return $this->hasMany('App\Order');
    }

    public function orders()
    {
        return $this->hasMany('App\Order');
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
            $origin = $store->storeDetail;
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
            $distance = $body->rows[0]->elements[0]->distance->value;
            return $distance * 0.000621371;
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
            'currency' => $currency
        ])->first();
        return !is_null($customer);
    }

    public function getStoreCustomer(
        $storeId,
        $stripe = true,
        $currency = 'USD',
        $gateway = Constants::GATEWAY_STRIPE
    ) {
        $store = Store::find($storeId);
        $customer = Customer::where([
            'user_id' => $this->id,
            'store_id' => $storeId,
            'currency' => $currency,
            'payment_gateway' => $gateway
        ])->first();

        if (!$stripe) {
            return $customer;
        }

        if ($gateway === Constants::GATEWAY_STRIPE) {
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
        $gateway = Constants::GATEWAY_STRIPE
    ) {
        $store = Store::find($storeId);

        $acct = $store->settings->stripe_account;
        \Stripe\Stripe::setApiKey($acct['access_token']);
        $stripeCustomer = \Stripe\Customer::create([
            'email' => $this->email,
            'description' => $this->name
        ]);
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $customer = new Customer();
        $customer->user_id = $this->id;
        $customer->store_id = $storeId;
        $customer->stripe_id = $stripeCustomer->id;
        $customer->currency = $currency;
        $customer->save();

        return $stripeCustomer;
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
        if (!$this->settings) {
            return false;
        }
        return $this->settings->notificationEnabled($notif);
    }

    public function sendNotification($notif, $data = [])
    {
        $email = null;

        if (!isset($data['user'])) {
            $data['user'] = $this;
        }

        switch ($notif) {
            case 'delivery_today':
                $email = new DeliveryToday($data);
                break;
            case 'meal_plan':
                $email = new MealPlan($data);
                break;
            case 'meal_plan_paused':
                $email = new MealPlanPaused($data);
                break;
            case 'new_order':
                $email = new NewOrder($data);
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
        }

        if ($email) {
            Mail::to($this)->send($email);
            return true;
        }

        return false;
    }
}
