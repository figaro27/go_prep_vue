<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuSetting extends Model
{
    protected $casts = [
        'redirectToCheckout' => 'boolean'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
