<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsChat extends Model
{
    protected $casts = [
        'unread' => 'boolean'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
