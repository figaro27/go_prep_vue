<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryFeeZipCode extends Model
{
    public $casts = [
        'shipping' => 'boolean'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
