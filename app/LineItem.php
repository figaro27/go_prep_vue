<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    protected $appends = ['full_title', 'base_size'];

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

    public function getFullTitleAttribute()
    {
        return $this->size !== null
            ? $this->size . ' - ' . $this->title
            : $this->title;
    }

    public function getBaseSizeAttribute()
    {
        return $this->size;
    }
}
