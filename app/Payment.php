<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'orders';

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
