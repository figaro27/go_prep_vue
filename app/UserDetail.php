<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable = [
        'firstname',
        'lastname',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'delivery'
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
        'notifications' => 'json'
    ];

    public function notificationEnabled($notif)
    {
        return isset($this->notifications[$notif]) &&
            $this->notifications[$notif];
    }
}
