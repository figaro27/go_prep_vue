<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreModule extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    protected $casts = [
        'allowCashOrders' => 'boolean'
    ];

    protected $fillable = ['allowCashOrders'];
}
