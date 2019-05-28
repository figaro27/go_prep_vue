<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealSubscriptionAddon extends Pivot
{
    protected $table = 'meal_subscription_addons';
    protected $appends = [];

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function mealSubscription()
    {
        return $this->belongsTo('App\MealSubscription');
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
