<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsContact extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
