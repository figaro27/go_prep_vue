<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealSubscription extends Model
{
    protected $table = 'meal_subscriptions';

    public function meals()
    {
        return $this->belongsTo('App\Meal');
    }

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    
}
