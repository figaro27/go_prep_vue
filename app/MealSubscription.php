<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealSubscription extends Pivot
{
    protected $table = 'meal_subscriptions';

    protected $appends = [];

    public function meals()
    {
        return $this->belongsTo('App\Meal');
    }

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function subscriptions()
    {
        return $this->hasMany('App\Subscription');
    }

    public function subscription()
    {
        return $this->belongsTo('App\Subscription');
    }
}
