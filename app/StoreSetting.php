<?php

namespace App;

use Carbon\Carbon;
use App\Model;
use Illuminate\Support\Facades\Cache;

class StoreSetting extends Model
{
    protected $fillable = [
        'minimum',
        'minimumOption',
        'showNutrition',
        'allowPickup',
        'pickupInstructions',
        'applyDeliveryFee',
        'deliveryFee',
        'stripe_account',
        'transferType',
        'notifications',
        'delivery_days',
        'view_delivery_days',
        'open'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['stripe_account', 'application_fee'];

    public $appends = [
        'next_delivery_dates',
        'next_orderable_delivery_dates',
        'stripe'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    protected $casts = [
        'open' => 'boolean',
        'showNutrition' => 'boolean',
        'showIngredients' => 'boolean',
        'applyDeliveryFee' => 'boolean',
        'applyProcessingFee' => 'boolean',
        'applyMealPlanDiscount' => 'boolean',
        'allowPickup' => 'boolean',
        'delivery_days' => 'array',
        'cutoff_days' => 'number',
        'cutoff_hours' => 'number',
        //'cutoff_time' => 'datetime:H:i',
        'delivery_distance_zipcodes' => 'json',
        'stripe_account' => 'json',
        'notifications' => 'json'
    ];

    public function setAttributeVisibility()
    {
        $user = auth('api')->user();

        if (!$user || !$user->hasRole('store')) {
            $this->setHidden([
                'stripe',
                'stripe_id',
                'stripe_account',
                'notifications',
                'user_id',
                'units'
            ]);
        }
    }

    public function getNextDeliveryDates($factorCutoff = false)
    {
        $dates = [];

        $now = Carbon::now('utc');

        $cutoff =
            $this->cutoff_days * (60 * 60 * 24) +
            $this->cutoff_hours * (60 * 60);

        $ddays = $this->delivery_days;

        foreach ($ddays as $day) {
            $date = Carbon::createFromFormat(
                'D',
                $day,
                $this->timezone
            )->setTime(0, 0, 0);

            $diff = $date->getTimestamp() - $now->getTimestamp();

            if ($factorCutoff) {
                $diff -= $cutoff;
            }

            if ($diff > 0) {
                $dates[] = $date;
            } else {
                $dates[] = $date->addWeek(1);
            }
        }

        usort($dates, function ($a, $b) {
            return $a->getTimestamp() - $b->getTimestamp();
        });

        foreach ($dates as $date) {
            $nextWeeksDate = Carbon::createFromFormat(
                'D',
                $day,
                $this->timezone
            )->setTime(0, 0, 0);
            $dates[] = $nextWeeksDate->addWeek(1);
        }

        return collect($dates);
    }

    public function getNextDeliveryDatesAttribute()
    {
        return $this->getNextDeliveryDates(false)->map(function (Carbon $date) {
            $cutoff = new Carbon($date);
            $cutoff->subSeconds($this->getCutoffSeconds());

            return [
                'date' => $date->toDateTimeString(),
                'date_passed' => $date->isPast(),
                'cutoff' => $cutoff->toDateTimeString(),
                'cutoff_passed' => $cutoff->isPast()
            ];
        });
    }
    public function getNextOrderableDeliveryDatesAttribute()
    {
        return $this->getNextDeliveryDates(true)->map(function (Carbon $date) {
            $cutoff = new Carbon($date);
            $cutoff->subSeconds($this->getCutoffSeconds());

            return [
                'date' => $date->toDateTimeString(),
                'date_passed' => $date->isPast(),
                'cutoff' => $cutoff->toDateTimeString(),
                'cutoff_passed' => $cutoff->isPast()
            ];
        });
    }

    public function getCutoffSeconds()
    {
        return $this->cutoff_days * (60 * 60 * 24) +
            $this->cutoff_hours * (60 * 60);
    }

    public function getStripeAttribute()
    {
        if ($this->stripe_account && isset($this->stripe_account->id)) {
            return $this->stripe_account;
        }
        return null;
    }

    public function notificationEnabled($notif)
    {
        return isset($this->notifications[$notif]) &&
            $this->notifications[$notif];
    }
}
