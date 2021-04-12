<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable = [
        'email',
        'firstname',
        'lastname',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'delivery',
        'companyname',
        'notifications',
        'billingAddress',
        'billingCity',
        'billingState',
        'billingZip',
        'store_id',
        'multiple_store_orders',
        'total_payments'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getFullNameAttribute()
    {
        return "{$this->firstname} {$this->lastname}";
    }

    protected $appends = ['full_name'];

    protected $casts = [
        'notifications' => 'json',
        'multiple_store_orders' => 'boolean'
    ];

    public function notificationEnabled($notif)
    {
        return isset($this->notifications[$notif]) &&
            $this->notifications[$notif];
    }
}
