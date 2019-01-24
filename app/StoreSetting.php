<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{

    protected $fillable = [
        'minimum',
        'showNutrition',
        'allowPickup',
        'pickupInstructions',
        'applyDeliveryFee',
        'deliveryFee',
        'stripe_account',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'stripe_account',
    ];

    public $appends = ['next_delivery_dates', 'stripe'];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    protected $casts = [
        'showNutrition' => 'boolean',
        'applyDeliveryFee' => 'boolean',
        'applyMealPlanDiscount' => 'boolean',
        'allowPickup' => 'boolean',
        'delivery_days' => 'array',
        'cutoff_days' => 'number',
        'cutoff_hours' => 'number',
        //'cutoff_time' => 'datetime:H:i',
        'delivery_distance_zipcodes' => 'json',
        'stripe_account' => 'json',
        'notifications' => 'json',
    ];

    public function getNextDeliveryDates()
    {
        $dates = [];

        $now = Carbon::now();

        $cutoff = $this->cutoff_days * (60 * 60 * 24) + $this->cutoff_hours * (60 * 60);

        foreach ($this->delivery_days as $day) {
            $date = Carbon::createFromFormat('D', $day)->setTime(0, 0, 0);

            $diff = $date->getTimestamp() - $now->getTimestamp();

            if ($now->format('N') <= $date->format('N') && $diff >= $cutoff) {
                $dates[] = $date;
            } else {
                $dates[] = $date->addWeek(1);
            }
        }

        usort($dates, function ($a, $b) {
            return $a->getTimestamp() - $b->getTimestamp();
        });

        return $dates;

    }

    public function getNextDeliveryDatesAttribute() {
      return $this->getNextDeliveryDates();
    }

    public function getStripeAttribute() {
      if($this->stripe_account && isset($this->stripe_account->id)) {
        return $this->stripe_account;
      }
      return null;
    }

    public function notificationEnabled($notif) {
      return isset($this->notifications[$notif]) && $this->notifications[$notif];
    }
}
