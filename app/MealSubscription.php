<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealSubscription extends Model
{
    protected $table = 'meal_subscriptions';

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

    public function subscriptions()
    {
        return $this->hasMany('App\Subscription');
    }

    public function subscription(){
    	return $this->belongsTo('App\Subscription');
    }

    
}
