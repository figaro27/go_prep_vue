<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use App\StorePlan;
use App\StorePlanTransaction;
use Illuminate\Support\Carbon;
use App\StoreSetting;
use App\Store;

class StorePlanController extends StoreController
{
    protected $stripe_subscription_id;
    protected $stripe_customer_id;

    // Regular constructor doesn't work
    public function construct()
    {
        if (env('APP_ENV') === 'production') {
            $key = 'sk_live_lyBbZ71GozcirrBo4LFEReX8';
        } else {
            $key = 'sk_test_T8zSIKcbQ0BA1QKLn19vAPzp';
        }
        \Stripe\Stripe::setApiKey($key);

        $storePlan = StorePlan::where('store_id', $this->store->id)->first();
        $this->stripe_subscription_id = $storePlan->stripe_subscription_id;
        $this->stripe_customer_id = $storePlan->stripe_customer_id;
    }

    public function getStorePlan()
    {
        return StorePlan::where('store_id', $this->store->id)->first();
    }

    public function getStripeSubscription()
    {
        $this->construct();
        if ($this->stripe_subscription_id) {
            $subscription = \Stripe\Subscription::retrieve(
                $this->stripe_subscription_id,
                []
            );
            return $subscription;
        }
    }

    public function getStorePlanTransactions()
    {
        return StorePlanTransaction::where('store_id', $this->store->id)
            ->orderBy('created', 'desc')
            ->get();

        // Originally had it getting invoices through Stripe before saving to store_plan_transactions

        //    $this->construct();
        // $invoices = \Stripe\Invoice::all([
        // 	'subscription' => $this->stripe_subscription_id,
        // 	'limit' => 100
        // ]);

        //    foreach ($invoices as $invoice){
        //        $payment_intent = \Stripe\PaymentIntent::retrieve($invoice->payment_intent, []);
        //        $card = $payment_intent['charges']['data'][0]['payment_method_details']['card'];
        //        $invoice->card = $card['brand'] . ' ' . $card['exp_month'] . '/' . substr($card['exp_year'], 2, 2);

        //        $invoice->finalized_at = Carbon::createFromTimestamp($invoice->finalized_at)->format('D, m/d/Y');
        //        $invoice->period_start = Carbon::createFromTimestamp($invoice->period_end)->format('D, m/d/Y');
        //        $invoice->period_end = Carbon::parse($invoice->period_start)->addMonths(1)->format('D, m/d/Y');
        //        $invoice->amount_paid = $invoice->amount_paid / 100;
        //    }

        // return $invoices;
    }

    public function getMonthToDateOrders()
    {
        $now = Carbon::now();
        $firstDayOfMonth = $now->firstOfMonth()->toDateTimeString();

        $orders = $this->store->orders
            ->where('paid_at', '>=', $firstDayOfMonth)
            ->count();

        $storePlan = StorePlan::where('store_id', $this->store->id)->first();
        if ($storePlan->joined_store_ids) {
            $joinedStoreIds = explode(',', $storePlan->joined_store_ids);
            foreach ($joinedStoreIds as $joinedStoreId) {
                $store = Store::where('id', $joinedStoreId)->first();
                $orders += $store->orders
                    ->where('paid_at', '>=', $firstDayOfMonth)
                    ->count();
            }
        }

        return $orders;
    }

    public function cancelSubscription(Request $request)
    {
        $this->construct();
        $reason = $request->get('reason');
        $additionalInfo = $request->get('additionalInfo');

        $storePlan = StorePlan::where('store_id', $this->store->id)->first();

        if ($storePlan->method !== 'connect') {
            $subscription = \Stripe\Subscription::retrieve(
                $this->stripe_subscription_id,
                []
            );
            $subscription->cancel();
        }

        $storePlan->status = 'cancelled';
        $storePlan->cancelled_at = Carbon::now();
        $storePlan->cancellation_reason = $reason;
        $storePlan->cancellation_additional_info = $additionalInfo;
        $storePlan->update();

        return $storePlan;
    }

    public function getStorePlanCards()
    {
        $this->construct();

        $data = \Stripe\PaymentMethod::all([
            'customer' => $this->stripe_customer_id,
            'type' => 'card'
        ]);

        if ($data) {
            $storePlan = StorePlan::where(
                'store_id',
                $this->store->id
            )->first();
            foreach ($data->data as $card) {
                if ($storePlan->stripe_card_id === $card->id) {
                    $card->default = true;
                } else {
                    $card->default = false;
                }
            }
            return $data->data;
        }
    }

    public function addStorePlanCard(Request $request)
    {
        $this->construct();

        // Add new card
        $cardId = $request->get('card')['id'];
        $customer = \Stripe\Customer::retrieve($this->stripe_customer_id, []);
        $customer->createSource($this->stripe_customer_id, [
            'source' => $request->get('token')
        ]);

        $sources = $customer->allSources($this->stripe_customer_id, []);

        $newlyAddedCard = null;

        foreach ($sources->data as $card) {
            if ($card->id === $cardId) {
                $newlyAddedCard = $card;
            }
        }

        // Update default payment method to this new card
        $customer->update($this->stripe_customer_id, [
            'default_source' => $newlyAddedCard->id
        ]);

        $storePlan = StorePlan::where('store_id', $this->store->id)->first();
        $storePlan->stripe_card_id = $newlyAddedCard->id;
        $storePlan->update();
    }

    public function updateStorePlanCard(Request $request)
    {
        $this->construct();
        $cardId = $request->get('card')['id'];
        $customer = \Stripe\Customer::retrieve($this->stripe_customer_id, []);
        $customer->update($this->stripe_customer_id, [
            'default_source' => $cardId
        ]);

        $storePlan = StorePlan::where('store_id', $this->store->id)->first();
        $storePlan->stripe_card_id = $cardId;
        $storePlan->update();
    }

    public function deleteStorePlanCard(Request $request)
    {
        $this->construct();
        $cardId = $request->get('card')['id'];
        $customer = \Stripe\Customer::retrieve($this->stripe_customer_id, []);
        $customer->deleteSource($this->stripe_customer_id, $cardId);
    }

    public function updatePlan(Request $request)
    {
        $this->construct();
        $storePlan = StorePlan::where('store_id', $this->store->id)->first();

        $selectedPlan = $request->get('plan');
        $selectedPeriod = $request->get('period');
        $plans = collect(config('plans'));
        $planObj = $plans[$selectedPlan][$selectedPeriod];
        $stripeId = $planObj['stripe_id'];
        $planAmount = $planObj['price'];
        $allowedOrders = $planObj['orders'];

        // Cancel existing subscription in Stripe
        if ($storePlan->method !== 'connect') {
            $subscription = \Stripe\Subscription::retrieve(
                $this->stripe_subscription_id,
                []
            );
            $invoices = \Stripe\Invoice::all([
                'subscription' => $subscription['id'],
                'limit' => 100
            ]);
            $lastChargeWasToday = Carbon::createFromTimestamp(
                end($invoices->data)['finalized_at']
            )->isToday();

            $subscription->cancel();

            // Create new subscription

            $currentChargeDay =
                $storePlan->plan_name !== 'pay-as-you-go'
                    ? $storePlan->day
                    : date('d');
            $currentDay = date('d');
            if ($currentChargeDay > $currentDay) {
                $trialPeriodDays = $currentChargeDay - $currentDay;
            } else {
                $date = Carbon::create(
                    date('Y'),
                    date('m'),
                    $currentChargeDay
                )->addMonthsNoOverflow(1);
                $now = Carbon::now();
                $trialPeriodDays = $date->diffInDays($now);
            }

            if ($trialPeriodDays === 0 && $lastChargeWasToday) {
                $trialPeriodDays = 30;
            }

            $subscription = \Stripe\Subscription::create([
                'customer' => $this->stripe_customer_id,
                'trial_period_days' => $trialPeriodDays,
                'items' => [
                    [
                        'plan' => $stripeId
                    ]
                ]
            ]);
        }

        // Update application fee if exists
        $storeSetting = StoreSetting::where(
            'store_id',
            $this->store->id
        )->first();
        $storeSetting->application_fee = 0;
        $storeSetting->update();

        // Update store plan
        $storePlan->plan_name = $selectedPlan;
        $storePlan->period = $selectedPeriod;
        $storePlan->amount = $planAmount;
        $storePlan->allowed_orders = $allowedOrders;
        $storePlan->stripe_subscription_id =
            $storePlan->method !== 'connect' ? $subscription['id'] : null;
        $storePlan->months_over_limit = 0;
        $storePlan->update();
        return $storePlan;
    }
}
