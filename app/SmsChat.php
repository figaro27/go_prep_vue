<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsChat extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
