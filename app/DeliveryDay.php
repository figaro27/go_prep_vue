<?php

namespace App;

use App\Traits\DeliveryDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;

class DeliveryDay extends Model
{
    use DeliveryDates;

    protected $appends = [
        'day_friendly',
        'day_short',
        'day_long',
        'pickup_location_ids'
    ];

    protected $casts = [
        'day' => 'number',
        'applyFee' => 'boolean',
        'cutoff_days' => 'number',
        'cutoff_hours' => 'number',
        'fee' => 'float',
        'active' => 'boolean',
        'minimum' => 'float'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

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

    public function next_orderable_dates()
    {
        $dates = array_merge(
            $this->store->settings->next_orderable_delivery_dates->toArray(),
            $this->store->settings->next_orderable_pickup_dates->toArray()
        );
        return $dates;
    }

    public function getDayFriendlyAttribute()
    {
        $nextDate = '';
        foreach (
            Collect($this->next_orderable_dates())
                ->reverse()
                ->toArray()
            as $date
        ) {
            if (
                $date['week_index'] == $this->day &&
                $date['type'] == $this->type
            ) {
                $nextDate = $date['day_friendly'];
            }
        }

        return $nextDate;

        // return $this->getDeliveryDateMultipleDelivery($this->day);
    }

    public function getDayShortAttribute()
    {
        return ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'][$this->day];
    }

    public function getDayLongAttribute()
    {
        return [
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday'
        ][$this->day];
    }

    public function getPickupLocationIdsAttribute()
    {
        return $this->pickup_locations->pluck('id');
    }

    public function getWeekIndex()
    {
        return $this->day;
    }

    public function isPastCutoff($dayFriendly, $type)
    {
        Artisan::call('cache:clear');
        $nextOrderableDates =
            $type == 'delivery'
                ? $this->store->settings->next_orderable_delivery_dates
                : $this->store->settings->next_orderable_pickup_dates;
        $dateIsAvailable = false;
        foreach ($nextOrderableDates as $nextOrderableDate) {
            if ($nextOrderableDate['day_friendly'] === $dayFriendly) {
                $dateIsAvailable = true;
            }
        }

        return $dateIsAvailable ? false : true;
    }
}
