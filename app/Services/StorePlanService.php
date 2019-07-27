<?php

namespace App\Services;

use App\Store;
use Illuminate\Database\Eloquent\Collection;
use App\StorePlan;
use App\StorePlanTransaction;
use Illuminate\Support\Carbon;
use Stripe\Stripe;
use Stripe\Charge;

class StorePlanService
{
    /**
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getRenewingPlans()
    {
        $now = Carbon::now();

        $dom = $now->day;
        $dow = $now->dayOfWeekIso;

        return StorePlan::where('active', 1)
            ->whereDate('last_charged', '<', Carbon::today())
            ->orWhere('last_charged', null)
            ->where(function ($query) use ($dom) {
                $query->where([
                    'period' => 'month',
                    'day' => $dom
                ]);
            })
            ->orWhere(function ($query) use ($dow) {
                $query->where([
                    'period' => 'week',
                    'day' => $dow
                ]);
            })
            ->get();
    }

    /**
     * @param App\StorePlan $plan
     */
    public function renew(StorePlan $plan)
    {
        if (!$plan->isToday()) {
            return false;
        }

        $charge = Charge::create([
            'amount' => $plan->amount,
            'currency' => $plan->currency,
            'source' => $plan->store->settings->stripe_id
        ]);

        $transaction = StorePlanTransaction::create(
            $plan,
            $charge->id,
            $charge->amount,
            $charge->currency,
            Carbon::createFromTimestamp($charge->created)
        );

        $plan->last_charged = Carbon::today();
        $plan->save();
    }
}
