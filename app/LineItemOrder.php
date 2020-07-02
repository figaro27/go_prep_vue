<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LineItemOrder extends Pivot
{
    protected $table = 'line_item_orders';

    protected $appends = [
        'title',
        'price',
        'size',
        'full_title',
        'base_size',
        'production_group_id'
    ];

    protected $casts = [
        'price' => 'float'
    ];

    public function lineItem()
    {
        return $this->belongsTo('App\LineItem');
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public function getTitleAttribute()
    {
        return $this->lineItem->title;
    }

    public function getFullTitleAttribute()
    {
        return $this->lineItem->size !== null
            ? $this->lineItem->size . ' - ' . $this->lineItem->title
            : $this->lineItem->title;
    }

    public function getPriceAttribute()
    {
        return $this->lineItem->price;
    }

    public function getSizeAttribute()
    {
        return $this->lineItem->size;
    }

    public function getBaseSizeAttribute()
    {
        return $this->lineItem->size;
    }

    public function getProductionGroupIdAttribute()
    {
        return $this->lineItem->production_group_id;
    }
}
