<?php

namespace App;

use App\User;
use App\Store;
use Illuminate\Support\Carbon;

class StorePlan extends Model
{
    protected $appends = ['period_name'];

    public function __toString()
    {
        return sprintf(
            '%s - %s %s',
            $this->store->details->name,
            '$' . number_format($this->amount / 100, 2),
            $this->period_name
        );
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function transactions()
    {
        return $this->hasMany('App\StorePlanTransaction');
    }

    public static function create(Store $store, $amount, $period, $day)
    {
        if ($store->plan) {
            return $store->plan->_update($amount, $period, $day);
        }

        $plan = new StorePlan();
        $plan->store_id = $store->id;
        $plan->active = true;
        $plan->amount = $amount;
        $plan->period = $period;
        $plan->day = $day;
        $plan->save();

        return $plan;
    }

    public function _update($amount, $period, $day)
    {
        $this->active = true;
        $this->amount = $amount;
        $this->period = $period;
        $this->day = $day;
        $this->save();
    }

    public function isToday()
    {
        if ($this->last_paid && $this->last_paid->isToday()) {
            return false;
        }

        if ($this->period === 'month') {
            return Carbon::now()->day === $this->day;
        } else {
            return Carbon::now()->dayOfWeekIso === $this->day;
        }
    }

    public function getPeriodNameAttribute()
    {
        $names = [
            'month' => 'Monthly',
            'week' => 'Weekly'
        ];
        return $names[$this->period];
    }
}
