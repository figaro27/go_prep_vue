<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderLabelSetting extends Model
{
    protected $table = 'order_label_settings';

    protected $fillable = ['store_id'];

    protected $casts = [
        'customer' => 'boolean',
        'address' => 'boolean',
        'phone' => 'boolean',
        'delivery' => 'boolean',
        'order_number' => 'boolean',
        'order_date' => 'boolean',
        'delivery_date' => 'boolean',
        'amount' => 'boolean',
        'balance' => 'boolean',
        'daily_order_number' => 'boolean',
        'pickup_location' => 'boolean',
        'website' => 'boolean',
        'social' => 'boolean'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
