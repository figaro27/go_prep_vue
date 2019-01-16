<?php

namespace App;

use App\Customer;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Stripe;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'stripe_id',
    ];

    protected $casts = [
        'stripe_account' => 'json',
    ];

    protected $appends = [
      'name',
    ];

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
        return $this->hasMany('App\UserSubscription');
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
            'admin' => 3,
        ];

        return $this->user_role_id === $roleMap[$role];
    }

    public function getNameAttribute() {
      return $this->userDetail->full_name;
    }

// Admin View

    public static function getCustomers()
    {
        return User::with('userDetail', 'order')->where('user_role_id', '=', 1)->get()->map(function ($user) {
            return [
                "id" => $user->id,
                "user_role_id" => $user->user_role_id,
                "Name" => $user->userDetail->firstname . ' ' . $user->userDetail->lastname,
                "phone" => $user->userDetail->phone,
                "address" => $user->userDetail->address,
                "city" => $user->userDetail->city,
                "state" => $user->userDetail->state,
                "Joined" => $user->created_at->format('m-d-Y'),
                "LastOrder" => optional($user->order->max("created_at"))->format('m-d-Y'),
                "TotalPayments" => $user->order->count(),
                "TotalPaid" => '$' . number_format($user->order->sum("amount"), 2, '.', ','),
            ];
        });
    }

//Store View

    public static function getStoreCustomers()
    {
        $id = Auth::user()->id;
        $customers = Order::all()->unique('user_id')->where('store_id', $id)->pluck('user_id');
        return User::with('userDetail', 'order')->whereIn('id', $customers)->get()->map(function ($user) {
            return [
                "id" => $user->id,
                "Name" => $user->userDetail->firstname . ' ' . $user->userDetail->lastname,
                "phone" => $user->userDetail->phone,
                "address" => $user->userDetail->address,
                "city" => $user->userDetail->city,
                "state" => $user->userDetail->state,
                "Joined" => $user->created_at->format('m-d-Y'),
                "LastOrder" => $user->order->max("created_at")->format('m-d-Y'),
                "TotalPayments" => $user->order->count(),
                "TotalPaid" => '$' . number_format($user->order->sum("amount"), 2, '.', ','),
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
            $origins[] = implode(',', [$origin->address, $origin->city, $origin->state, $origin->zip, $origin->country]);
        }
        $dest = $this->userDetail;

        $query = [
            'origins' => implode('|', $origins),
            'destinations' => implode(',', [$dest->address, $dest->city, $dest->state, $dest->zip]),
            'key' => config('google.api_key'),
        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'https://maps.googleapis.com/maps/api/distancematrix/json', [
            'query' => $query,
        ]);
        $status = $res->getStatusCode();
// "200"
        $body = json_decode((string) $res->getBody());

        try {
            return $body->rows[0]->elements[0]->distance->value;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function hasCustomer($storeId)
    {
        $customer = Customer::where(['user_id' => $this->id, 'store_id' => $storeId])->first();
        return !is_null($customer);
    }

    public function getCustomer($storeId)
    {
        $customer = Customer::where(['user_id' => $this->id, 'store_id' => $storeId])->first();
        return $customer;
    }

    public function createCustomer($storeId, $token)
    {
        $stripeCustomer = \Stripe\Customer::create([
            "source" => $token,
        ]);

        $customer = new Customer;
        $customer->user_id = $this->id;
        $customer->store_id = $storeId;
        $customer->stripe_id = $stripeCustomer->id;
        $customer->save();
    }

}
