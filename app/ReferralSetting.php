<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralSetting extends Model
{
    protected $appends = ['amountFormat', 'url'];

    protected $fillable = ['store_id'];

    protected $casts = [
        'enabled' => 'boolean',
        'signupEmail' => 'boolean',
        'showInNotifications' => 'boolean',
        'showInMenu' => 'boolean',
        'type' => 'string',
        'frequency' => 'string'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function getAmountFormatAttribute()
    {
        if ($this->type === 'percent') {
            return (int) ltrim($this->amount, ".") . '%';
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
            '.com/customer/menu?r=';
    }
}
