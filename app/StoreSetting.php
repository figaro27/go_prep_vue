<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{

    protected $fillable = [
        'minimum', 'showNutrition', 'allowPickup', 'pickupInstructions', 'applyDeliveryFee', 'deliveryFee',
    ];

    public $appends = ['next_delivery_dates'];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    protected $casts = [
        'showNutrition' => 'boolean',
        'applyDeliveryFee' => 'boolean',
        'allowPickup' => 'boolean',
        'delivery_days' => 'array',
        'cutoff_days' => 'number',
        'cutoff_hours' => 'number',
        //'cutoff_time' => 'datetime:H:i',
        'delivery_distance_zipcodes' => 'json',
    ];

    public static function boot()
    {
        parent::boot();

        self::saving(function ($model) {
            $model->delivery_days = json_encode($model->delivery_days);
            return $model;
        });

    }

    public function getNextDeliveryDatesAttribute()
    {
        $dates = [];

        $today = Carbon::today();

        $cutoff = $this->cutoff_days * (60 * 60 * 24) + $this->cutoff_hours * (60 * 60);

        foreach ($this->delivery_days as $day) {
            $date = Carbon::createFromFormat('D', $day);

            $diff = $date->getTimestamp() - $today->getTimestamp();

            if ($today->format('N') <= $date->format('N') && $diff >= $cutoff) {
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
}
