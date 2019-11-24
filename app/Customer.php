<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Store;
use Auth;

class Customer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['store_id', 'user_id', 'currency'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['stripe_id', 'user'];

    protected $casts = [];

    protected $appends = [
        'joined',
        'first_order',
        'last_order',
        'total_payments',
        'total_paid',
        'paid_orders',
        'name',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'delivery',
        'email'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function cards()
    {
        return $this->hasManyThrough('App\Card', 'App\User');
    }

    public function orders()
    {
        return $this->hasMany('App\Order')->orderBy('created_at', 'desc');
    }

    public function getStoreID()
    {
        $id = Auth::user()->id;
        $storeID = Store::where('user_id', $id)
            ->pluck('id')
            ->first();
        return $storeID;
    }

    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    public function getPaidOrdersAttribute()
    {
        return $this->orders->where('paid', 1);
    }

    public function getJoinedAttribute()
    {
        return $this->user->created_at->format('m/d/Y');
    }

    public function getFirstOrderAttribute()
    {
        if (count($this->user->order) === 0) {
            return $this->user->created_at->format('m/d/Y');
        }
        $date = $this->user->order
            ->where('store_id', $this->getStoreID())
            ->min("created_at");
        return $date ? $date->format('F d, Y') : null;
    }

    public function getLastOrderAttribute()
    {
        $date = $this->user->order
            ->where('store_id', $this->getStoreID())
            ->max("created_at");
        return $date ? $date->format('m/d/Y') : null;
    }

    public function getTotalPaymentsAttribute()
    {
        return $this->user->order
            ->where('store_id', $this->getStoreID())
            ->where('paid', 1)
            ->count();
    }

    public function getTotalPaidAttribute()
    {
        return $this->user->order
            ->where('store_id', $this->getStoreID())
            ->where('paid', 1)
            ->sum("amount");
    }

    public function getNameAttribute()
    {
        return $this->user->name;
    }

    public function getPhoneAttribute()
    {
        return $this->user->userDetail->phone;
    }
    public function getAddressAttribute()
    {
        return $this->user->userDetail->address;
    }
    public function getCityAttribute()
    {
        return $this->user->userDetail->city;
    }
    public function getStateAttribute()
    {
        return $this->user->userDetail->state;
    }
    public function getZipAttribute()
    {
        return $this->user->userDetail->zip;
    }

    public function getDeliveryAttribute()
    {
        return $this->user->userDetail->delivery;
    }
}
