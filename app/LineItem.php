<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function order()
    {
        return $this->belongsTo('App\Order');
    }
}
