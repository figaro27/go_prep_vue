<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealSubscription extends Pivot
{
    protected $table = 'meal_subscriptions';

    protected $appends = ['price'];

    public function meals()
    {
        return $this->belongsTo('App\Meal');
    }

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function meal_size()
    {
        return $this->belongsTo('App\MealSize');
    }

    public function subscriptions()
    {
        return $this->hasMany('App\Subscription');
    }

    public function subscription()
    {
        return $this->belongsTo('App\Subscription');
    }

    public function getPriceAttribute()
    {
        return $this->meal_size_id
            ? $this->meal_size->price
            : $this->meal->price;
    }
}
