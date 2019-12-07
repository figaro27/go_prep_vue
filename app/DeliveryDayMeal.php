<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryDayMeal extends Model
{
    public function meal()
    {
        return $this->hasOne('App\OptimizedMeal', 'id', 'meal_id');
    }
}
