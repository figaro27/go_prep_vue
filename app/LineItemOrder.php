<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LineItemOrder extends Pivot
{
    protected $table = 'line_item_';

    public function lineItem()
    {
        return $this->belongsTo('App\LineItem');
    }

    public function order()
    {
        return $this->belongsTo('App\Order');
    }
}
