<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealOrderAddon extends Pivot
{
    protected $table = 'meal_order_addons';
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

    public function addon()
    {
        return $this->belongsTo('App\MealAddon', 'meal_addon_id');
    }
}
