<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealOrder extends Pivot
{
    protected $table = 'meal_orders';

    protected $appends = ['quantity'];

    public function getQuantityAttribute()
    {
        return $this->pivot->quantity;
    }

    public function meals()
    {
        return $this->belongsTo('App\Meal');
    }

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    public function order()
    {
        return $this->belongsTo('App\Order');
    }
}
