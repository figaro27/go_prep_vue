<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealSubscriptionComponent extends Pivot
{
    protected $table = 'meal_subscription_components';
    protected $appends = [];

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function mealSubscription()
    {
        return $this->belongsTo('App\MealSubscription');
    }

    public function subscription()
    {
        return $this->belongsTo('App\Subscription');
    }

    public function component()
    {
        return $this->belongsTo('App\MealComponent', 'meal_component_id');
    }

    public function option()
    {
        return $this->belongsTo(
            'App\MealComponentOption',
            'meal_component_option_id'
        );
    }
}
