<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralRule extends Model
{
    protected $appends = ['amountFormat', 'url'];

    protected $casts = [
        'enabled' => 'boolean',
        'signupEmail' => 'boolean',
        'showInNotifications' => 'boolean',
        'showInMenu' => 'boolean',
        'type' => 'string'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function getAmountFormatAttribute()
    {
        if ($this->type === 'percent') {
            return trim($this->amount, ".00") . '%';
        } else {
            return '$' . $this->amount;
        }
    }

    public function getUrlAttribute()
    {
        $host = $this->store->details->host
            ? $this->store->details->host
            : 'goprep';
        return 'https://' .
            $this->store->details->domain .
            '.' .
            $host .
            '.com/?r=';
    }
}
