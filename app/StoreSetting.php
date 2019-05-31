<?php

namespace App;

use App\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        'open',
        'meal_packages',
        'currency'
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
        'subscribed_delivery_days', // Delivery days with active meal plans
        'stripe'
    ];

    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            $model->store->clearCaches();
        });
    }

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
        'meal_packages' => 'boolean',
        'delivery_days' => 'array',
        'cutoff_days' => 'number',
        'cutoff_hours' => 'number',
        //'cutoff_time' => 'datetime:H:i',
        'delivery_distance_zipcodes' => 'json',
        'stripe_account' => 'json',
        'notifications' => 'json',
        'deliveryFee' => 'float',
        'processingFee' => 'float',
        'application_fee' => 'float',
        'mealPlanDiscount' => 'float'
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

    /**
     * Get the cutoff date for a particular delivery date
     *
     * @param Carbon $deliveryDate
     * @return Carbon $cutoffDate
     */
    public function getCutoffDate(Carbon $deliveryDate)
    {
        $cutoffDate = Carbon::createFromDate(
            $deliveryDate->year,
            $deliveryDate->month,
            $deliveryDate->day,
            $this->timezone
        );
        if ($this->cutoff_type === 'timed') {
            return $cutoffDate
                ->setTime(0, 0, 0)
                ->subSeconds($this->getCutoffSeconds())
                ->setTimezone('utc');
        } elseif ($this->cutoff_type === 'single_day') {
            $dayName = date(
                'l',
                strtotime("Sunday +{$this->cutoff_days} days")
            );
            return $cutoffDate
                ->modify('last ' . $dayName)
                ->setTime($this->cutoff_hours, 0, 0)
                ->setTimezone('utc');
        }
    }

    public function getNextDeliveryDates($factorCutoff = false)
    {
        return Cache::remember(
            'store_' .
                $this->store_id .
                'delivery_dates' .
                ($factorCutoff ? 1 : 0),
            1,
            function () use ($factorCutoff) {
                $dates = [];

                $now = Carbon::now('utc');

                $cutoff =
                    $this->cutoff_days * (60 * 60 * 24) +
                    $this->cutoff_hours * (60 * 60);

                $ddays = $this->delivery_days;

                if (!is_array($ddays)) {
                    return collect([]);
                }

                foreach ($ddays as $i => $day) {
                    $date = Carbon::createFromFormat(
                        'D',
                        $day,
                        $this->timezone
                    )->setTime(0, 0, 0);

                    $cutoff = $this->getCutoffDate($date);

                    if (!$factorCutoff || !$cutoff->isPast()) {
                        $dates[] = $date;
                    } else {
                        $dates[] = $date->addWeek(1);
                    }
                }

                // foreach ($dates as $date) {
                //     $dates[] = $date->copy()->addWeek(1);
                // }

                usort($dates, function ($a, $b) {
                    return $a->getTimestamp() - $b->getTimestamp();
                });

                return collect($dates);
            }
        );
    }

    public function getNextDeliveryDatesAttribute()
    {
        return $this->getNextDeliveryDates(false)->map(function (Carbon $date) {
            $cutoff = $this->getCutoffDate($date);

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
            $cutoff = $this->getCutoffDate($date);

            return [
                'date' => $date->toDateTimeString(),
                'date_passed' => $date->isPast(),
                'cutoff' => $cutoff->toDateTimeString(),
                'cutoff_passed' => $cutoff->isPast()
            ];
        });
    }

    public function getSubscribedDeliveryDaysAttribute()
    {
        return Cache::remember(
            'store_' . $this->store_id . '_subscribed_delivery_days',
            10,
            function () {
                $days = DB::table('subscriptions')
                    ->select(DB::raw('delivery_day, count(*) as `count`'))
                    ->where([
                        'status' => 'active',
                        'store_id' => $this->store->id
                    ])
                    ->groupBy('delivery_day')
                    ->get();

                $ddays = [];
                foreach ($days as $day) {
                    if ($day->count > 0) {
                        $ddays[] = strtolower(
                            date(
                                'D',
                                strtotime("Sunday +{$day->delivery_day} days")
                            )
                        );
                    }
                }

                return $ddays;
            }
        );
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
