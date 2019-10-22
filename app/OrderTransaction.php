<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderTransaction extends Model
{
    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }

    public function card()
    {
        return $this->belongsTo('App\Card');
    }
}
