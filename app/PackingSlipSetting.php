<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackingSlipSetting extends Model
{
    protected $casts = [
        'hide_pricing' => 'boolean'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
