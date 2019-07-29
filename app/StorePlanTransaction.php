<?php

namespace App;

use App\User;
use App\Store;
use Illuminate\Support\Carbon;

class StorePlanTransaction extends Model
{
    protected $appends = [];

    public function plan()
    {
        return $this->belongsTo('App\StorePlan');
    }

    /**
     * @param StorePlan $plan
     */
    public static function create(
        StorePlan $plan,
        string $stripeId,
        int $amount,
        string $currency,
        Carbon $timestamp
    ) {
        $transaction = new self();
        $transaction->store_plan_id = $plan->id;
        $transaction->stripe_id = $stripeId;
        $transaction->amount = $amount;
        $transaction->currency = $currency;
        $transaction->timestamp = $timestamp;
        $transaction->save();

        return $transaction;
    }

    public function _update()
    {
    }
}
