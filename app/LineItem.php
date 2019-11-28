<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function lineItemOrders()
    {
        return $this->belongsToMany('App\LineItemOrder')->withPivot(
            'quantity',
            'production_group_id'
        );
    }

    public function orders()
    {
        return $this->belongsToMany('App\Order', 'line_item_orders');
    }

    protected $casts = [
        'price' => 'float'
    ];
}
