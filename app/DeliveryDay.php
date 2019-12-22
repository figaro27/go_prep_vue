<?php

namespace App;

use App\Traits\DeliveryDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DeliveryDay extends Model
{
    use DeliveryDates;

    protected $appends = ['day_friendly', 'day_short', 'pickup_location_ids'];

    protected $casts = [
        'day' => 'number',
        'applyFee' => 'boolean',
        'cutoff_days' => 'number',
        'cutoff_hours' => 'number',
        'fee' => 'float'
    ];

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

    public function pickup_locations()
    {
        return $this->belongsToMany(
            'App\PickupLocation',
            'delivery_day_pickup_locations'
        );
    }

    public function getDayFriendlyAttribute()
    {
        return $this->getDeliveryDateMultipleDelivery($this->day);
    }

    public function getDayShortAttribute()
    {
        return ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'][$this->day];
    }

    public function getPickupLocationIdsAttribute()
    {
        return $this->pickup_locations->pluck('id');
    }

    public function getWeekIndex()
    {
        return $this->day;
    }
}
