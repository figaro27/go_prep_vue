<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
