<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PickupLocation extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function order()
    {
        return $this->belongsToMany('App\Order');
    }

    public function subscription()
    {
        return $this->belongsToMany('App\Subscription');
    }
}
