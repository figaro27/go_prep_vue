<?php

namespace App;
use App\User;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $appends = ['referredCustomers'];

    protected $casts = [
        'code' => 'string'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getReferredCustomersAttribute()
    {
        return 'turtles';
    }
}
