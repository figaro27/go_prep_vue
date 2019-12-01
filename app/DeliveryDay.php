<?php

namespace App;

use App\Traits\DeliveryDates;
use Illuminate\Database\Eloquent\Model;

class DeliveryDay extends Model
{
    use DeliveryDates;

    protected $appends = ['day_friendly'];

    public function meals()
    {
        return $this->hasManyThrough(
            'App\Meal',
            'App\DeliveryDayMealPackage',
            'delivery_day_id',
            'id'
        );
    }

    public function meal_packages()
    {
        return $this->hasManyThrough(
            'App\MealPackage',
            'App\DeliveryDayMeal',
            'delivery_day_id',
            'id'
        );
    }

    public function getDayFriendlyAttribute()
    {
        return $this->getDeliveryDateMultipleDelivery($this->day);
    }
}
