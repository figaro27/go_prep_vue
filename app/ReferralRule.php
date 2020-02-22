<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralRule extends Model
{
    protected $casts = [
        'signupEmail' => 'boolean',
        'showInNotifications' => 'boolean',
        'showInMenu' => 'boolean',
        'type' => 'string'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
