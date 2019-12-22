<?php

namespace App;

use App\Model;
use App\DeliveryDay;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\OmittedDeliveryDates;

class StoreSetting extends Model
{
    /**
     * @var DeliveryDay $deliveryDay
     */
    protected $deliveryDay = null;

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
        'currency',
        'showMacros'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'stripe_account',
        'application_fee',
        'authorize_transaction_key'
    ];

    public $appends = [
        'next_delivery_dates',
        'next_orderable_delivery_dates',
        'next_orderable_pickup_dates',
        'subscribed_delivery_days', // Delivery days with active subscriptionss
        'stripe',
        'currency_symbol',
        'date_format'
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
        'showMacros' => 'boolean',
        'showIngredients' => 'boolean',
        'applyDeliveryFee' => 'boolean',
        'applyProcessingFee' => 'boolean',
        'allowMealPlans' => 'boolean',
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
        'mealPlanDiscount' => 'float',
        'mealInstructions' => 'boolean',
        'enableSalesTax' => 'boolean'
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
     * Sets the delivery day for cutoff calculations etc.
     *
     * @param DeliveryDay $deliveryDay
     * @param boolean $pickup
     */
    public function setDeliveryDayContext(DeliveryDay $deliveryDay, $pickup = 0)
    {
        $this->deliveryDay = $deliveryDay;

        $this->applyDeliveryFee = $deliveryDay->applyFee;
        $this->deliveryFee = $deliveryDay->fee;
        $this->cutoff_type = $deliveryDay->cutoff_type;
        $this->cutoff_days = $deliveryDay->cutoff_days;
        $this->cutoff_hours = $deliveryDay->cutoff_hours;
        $this->mileageBase = $deliveryDay->mileageBase;
        $this->mileagePerMile = $deliveryDay->mileagePerMile;
    }

    /**
     * Resets the delivery day context
     */
    public function clearDeliveryDayContext()
    {
        $this->deliveryDay = null;

        $this->applyDeliveryFee = $this->getOriginal('applyDeliveryFee');
        $this->deliveryFee = $this->getOriginal('deliveryFee');
        $this->cutoff_type = $this->getOriginal('cutoff_type');
        $this->cutoff_days = $this->getOriginal('cutoff_days');
        $this->cutoff_hours = $this->getOriginal('cutoff_hours');
        $this->mileageBase = $this->getOriginal('mileageBase');
        $this->mileagePerMile = $this->getOriginal('mileagePerMile');
    }

    /**
     * Get the cutoff date for a particular delivery date
     *
     * @param Carbon $deliveryDate
     * @return Carbon $cutoffDate
     */
    public function getCutoffDate(
        Carbon $deliveryDate,
        DeliveryDay $deliveryDayContext = null
    ) {
        if ($deliveryDayContext) {
            $this->setDeliveryDayContext($deliveryDayContext);
        }

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

        $this->clearDeliveryDayContext();
    }

    public function getNextDeliveryDates(
        $factorCutoff = false,
        $type = 'delivery'
    ) {
        return Cache::remember(
            'store_' .
                $this->store_id .
                'delivery_dates' .
                ($factorCutoff ? 1 : 0) .
                $type,
            1,
            function () use ($factorCutoff, $type) {
                $modules = $this->store->modules;
                $dates = [];

                $now = Carbon::now('utc');

                $cutoff =
                    $this->cutoff_days * (60 * 60 * 24) +
                    $this->cutoff_hours * (60 * 60);

                $customDeliveryDays = $this->store->isModuleEnabled(
                    'customDeliveryDays'
                );
                $ddays = $customDeliveryDays
                    ? $this->store->deliveryDays->filter(function (
                        DeliveryDay $dday
                    ) use ($type) {
                        return $dday->type === $type;
                    })
                    : $this->delivery_days;

                if (!count($ddays)) {
                    return collect([]);
                }

                foreach ($ddays as $i => $day) {
                    $customDeliveryDay = null;

                    if ($customDeliveryDays) {
                        $customDeliveryDay = $day;
                        $day = $customDeliveryDay->day_short;
                        $this->setDeliveryDayContext($customDeliveryDay);
                    }

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

                    if ($customDeliveryDays) {
                        $this->clearDeliveryDayContext();
                    }
                }

                $deliveryWeeks = $this->deliveryWeeks;
                $upcomingWeeksDates = [];

                foreach ($dates as $date) {
                    for ($i = 1; $i <= $deliveryWeeks; $i++) {
                        $upcomingWeeksDates[] = $date->copy()->addWeek($i);
                    }
                }

                $allDates = array_merge($dates, $upcomingWeeksDates);

                $omittedDeliveryDates = OmittedDeliveryDates::where(
                    'store_id',
                    $this->store->id
                )->get();
                foreach ($omittedDeliveryDates as $omittedDeliveryDate) {
                    if (in_array($omittedDeliveryDate->date, $allDates)) {
                        unset(
                            $allDates[
                                array_search(
                                    $omittedDeliveryDate->date,
                                    $allDates
                                )
                            ]
                        );
                    }
                }

                usort($allDates, function ($a, $b) {
                    return $a->getTimestamp() - $b->getTimestamp();
                });

                return collect($allDates);
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
                'cutoff_passed' => $cutoff->isPast(),
                'week_index' => (int) $date->format('w')
            ];
        });
    }
    public function getNextOrderableDeliveryDatesAttribute()
    {
        return $this->getNextDeliveryDates(true)->map(function (Carbon $date) {
            $deliveryDay = null; //$this->store->getDeliveryDayByWeekIndex($date->format('w'));
            $cutoff = $this->getCutoffDate($date, $deliveryDay);

            return [
                'date' => $date->toDateTimeString(),
                'date_passed' => $date->isPast(),
                'cutoff' => $cutoff->toDateTimeString(),
                'cutoff_passed' => $cutoff->isPast(),
                'week_index' => (int) $date->format('w')
            ];
        });
    }
    public function getNextOrderablePickupDatesAttribute()
    {
        return $this->getNextDeliveryDates(true, 'pickup')->map(function (
            Carbon $date
        ) {
            $deliveryDay = null; //$this->store->getDeliveryDayByWeekIndex($date->format('w'));
            $cutoff = $this->getCutoffDate($date, $deliveryDay);

            return [
                'date' => $date->toDateTimeString(),
                'date_passed' => $date->isPast(),
                'cutoff' => $cutoff->toDateTimeString(),
                'cutoff_passed' => $cutoff->isPast(),
                'week_index' => (int) $date->format('w')
            ];
        });
    }

    public function getDeliveryDay(Carbon $date)
    {
        return $this->store->deliveryDays
            ->where('day', $date->format('w'))
            ->first();
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

    public function getCurrencySymbolAttribute()
    {
        switch ($this->currency) {
            case "USD":
                return '$';
                break;
            case "GBP":
                return '£';
                break;
        }
    }

    public function getDateFormatAttribute()
    {
        if ($this->currency === 'USD') {
            return 'D, m/d/Y';
        } else {
            return 'D, d/m/Y';
        }
    }
}
