<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryDay extends Model
{
    public function meals()
    {
        return $this->hasManyThrough(
            'App\Meal',
            'App\DeliveryDayMeal',
            'delivery_day_id',
            'id'
        );
    }
}
