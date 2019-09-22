<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LineItemOrder extends Pivot
{
    protected $table = 'line_item_orders';

    protected $appends = ['title', 'price'];

    protected $casts = [
        'price' => 'float'
    ];

    public function lineItem()
    {
        return $this->belongsTo('App\LineItem');
    }

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public function getTitleAttribute()
    {
        return $this->lineItem->title;
    }

    public function getPriceAttribute()
    {
        return $this->lineItem->price;
    }
}
