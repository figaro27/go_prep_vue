<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealOrderComponent extends Pivot
{
    protected $table = 'meal_order_components';
    protected $appends = [];

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function mealOrder()
    {
        return $this->belongsTo('App\MealOrder');
    }

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public function component()
    {
        return $this->belongsTo('App\MealComponent');
    }

    public function option()
    {
        return $this->belongsTo('App\MealComponentOption');
    }
}
