<?php

namespace App;

use App\User;
use App\Store;
use Illuminate\Support\Carbon;

class StorePlan extends Model
{
    // public function __toString()
    // {
    //     return sprintf(
    //         '%s - %s %s',
    //         $this->store->details->name,
    //         '$' . number_format($this->amount / 100, 2),
    //         $this->period_name
    //     );
    // }

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
        $plan->store_name = $store->details->name;
        $plan->amount = $amount;
        $plan->period = $period;
        $plan->day = $day;
        $plan->save();

        return $plan;
    }

    public function _update($amount, $period, $day)
    {
        $this->amount = $amount;
        $this->period = $period;
        $this->day = $day;
        $this->save();
    }
}
