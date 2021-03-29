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
        $month = $now->month;

        return StorePlan::where('status', 'active')
            ->where(function ($query) use ($dom) {
                $query->where([
                    'period' => 'monthly',
                    'day' => $dom
                ]);
            })
            ->orWhere(function ($query) use ($dom, $month) {
                $query->where([
                    'period' => 'annually',
                    'day' => $dom,
                    'month' => $month
                ]);
            })
            ->get();
    }

    /**
     * @param App\StorePlan $plan
     */
    public function renew(StorePlan $plan)
    {
        if (
            $plan->status == 'cancelled' ||
            !$plan->store->settings->stripe_id
        ) {
            return false;
        }

        $charge = Charge::create([
            'amount' => $plan->amount,
            'currency' => $plan->currency,
            'source' => $plan->store->settings->stripe_id,
            'description' => 'GoPrep subscription renewal (bank withdrawal)'
        ]);

        $plan->last_charged = Carbon::today();
        $plan->update();
    }
}
