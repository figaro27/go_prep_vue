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
            ->whereDate('last_charged', '<', Carbon::today())
            ->orWhere('last_charged', null)
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
            'source' => $plan->store->settings->stripe_id
        ]);

        $storePlanTransaction = new StorePlanTransaction();
        $storePlanTransaction->store_id = $plan->store_id;
        $storePlanTransaction->store_plan_id = $plan->id;
        $storePlanTransaction->stripe_id = $charge->id;
        $storePlanTransaction->amount = $charge->amount;
        $storePlanTransaction->currency = $plan->currency;
        $storePlanTransaction->receipt_url = $charge->receipt_url;
        $storePlanTransaction->period_start = Carbon::now()->toDateTimeString();
        $storePlanTransaction->period_end =
            $plan->period == 'monthly'
                ? Carbon::now()
                    ->addMonths(1)
                    ->toDateTimeString()
                : Carbon::now()
                    ->addYears(1)
                    ->toDateTimeString();
        $storePlanTransaction->save();

        $plan->last_charged = Carbon::today();
        $plan->update();
    }
}
