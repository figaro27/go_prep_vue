<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderEvent extends Model
{

    protected $fillable = [
      'type', 'stripe_event'
    ];

    protected $hidden = [

    ];

    protected $casts = [

    ];

    protected $appends = [];

    public function order()
    {
        return $this->belongsTo('App\Order');
    }
}
