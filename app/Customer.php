<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'stripe_id',
      'user',
    ];

    protected $casts = [
    ];

    protected $appends = [
        'joined',
        'first_order',
        'last_order',
        'total_payments',
        'total_paid',
        'name',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'delivery',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function cards() {
      return $this->hasManyThrough('App\Card', 'App\User');
    }

    public function orders() {
      return $this->hasManyThrough(
        'App\Order',
        'App\User',
        'id', // Foreign key on users table...
        'user_id', // Foreign key on orders table...
        'user_id', // Local key on customers table...
        'id' // Local key on users table...
      );
    }

    public function getJoinedAttribute()
    {
        return $this->user->created_at->format('F d, Y');
    }

    public function getFirstOrderAttribute()
    {
        return $this->user->order->min("created_at")->format('F d, Y');
    }

    public function getLastOrderAttribute()
    {
        return $this->user->order->max("created_at")->format('F d, Y');
    }

    public function getTotalPaymentsAttribute()
    {
        return $this->user->order->count();
    }

    public function getTotalPaidAttribute()
    {
        return $this->user->order->sum("amount");
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
