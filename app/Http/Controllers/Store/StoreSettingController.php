<?php

namespace App\Http\Controllers\Store;

use App\StoreSetting;
use App\Subscription;
use App\Customer;
use App\Store;
use App\Mail\Customer\MealPLanPaused;
use App\Mail\Customer\SubscriptionCancelled;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateSettings;
use Auth;

class StoreSettingController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->settings;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\StoreSetting  $storeSetting
     * @return \Illuminate\Http\Response
     */
    public function show(StoreSetting $storeSetting)
    {
        $id = Auth::user()->id;
        $settings = StoreSetting::findOrFail($id);
        return $settings;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\StoreSetting  $storeSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(StoreSetting $storeSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StoreSetting  $storeSetting
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSettings $request, StoreSetting $storeSetting)
    {
        $settings = StoreSetting::where('store_id', $this->store->id);

        $values = $request->except([
            'next_delivery_dates',
            'next_orderable_delivery_dates',
            'subscribed_delivery_days',
            'stripe',
            'store',
            'currency_symbol',
            'date_format',
            'next_orderable_pickup_dates',
            'menuReopening',
            'next_orderable_dates'
        ]);
        $values['delivery_days'] = json_encode($values['delivery_days']);
        $values['notifications'] = json_encode($values['notifications']);
        $values['color'] = isset($values['color'])
            ? $values['color']
            : $settings->first()->color;
        $values['delivery_distance_zipcodes'] = json_encode(
            $values['delivery_distance_zipcodes']
        );

        // Preventing the bug which causes transferType to save as null
        if (
            !isset($values['transferType']) ||
            $values['transferType'] === "" ||
            $values['transferType'] === null
        ) {
            $values['transferType'] = $settings->first()->transferType;
        }

        $subDiscount = $settings->first()->applyMealPlanDiscount;

        $updateStatementDescriptor = false;
        if (
            (isset($values['statementDescriptor']) &&
                $values['statementDescriptor'] !==
                    $settings->first()->statementDescriptor) ||
            !$settings->first()->statementDescriptor
        ) {
            $updateStatementDescriptor = true;
        }

        $settings->update($values);

        // If the statement descriptor was updated, update it in Stripe
        if ($updateStatementDescriptor) {
            $this->updateStatementDescriptor();
        }

        // Removing subscription discount from all active subscriptions if turned off
        if ($subDiscount && !$values['applyMealPlanDiscount']) {
            $subs = Subscription::where('store_id', $this->store->id);
            $subs = $subs
                ->where('status', 'active')
                ->orWhere('status', 'paused')
                ->get();
            foreach ($subs as $sub) {
                $sub->mealPlanDiscount = 0.0;
                $sub->update();
                $sub->syncPrices();
            }
        }

        $store = Store::where('id', $this->store->id)->first();
        if ($values['open'] === true) {
            $store->accepted_toa = 1;
            $store->update();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StoreSetting  $storeSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(StoreSetting $storeSetting)
    {
        //
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createStripeAccount(Request $request)
    {
        if ($this->store->hasStripe()) {
            return;
        }

        $settings = $this->store->settings;

        $account = Stripe\Account::create([
            "type" => "standard",
            "country" => "US",
            "email" => $this->store->user->userDetail->email
        ]);

        if (!isset($account->id)) {
            return null;
        }
    }

    public function pauseAllSubscriptions()
    {
        foreach ($this->store->subscriptions as $sub) {
            if ($sub->status !== 'cancelled') {
                $sub->pause();
            }
        }
    }

    public function cancelAllSubscriptions()
    {
        foreach ($this->store->subscriptions as $sub) {
            if ($sub->status !== 'cancelled') {
                $sub->cancel();
            }
        }
    }

    public function cancelSubscriptionsByDeliveryDay(Request $request)
    {
        $deliveryDay = $request->deliveryDay;
        $day = null;
        switch ($deliveryDay) {
            case "mon":
                $day = 1;
                break;

            case "tue":
                $day = 2;
                break;

            case "wed":
                $day = 3;
                break;

            case "thu":
                $day = 4;
                break;

            case "fri":
                $day = 5;
                break;

            case "sat":
                $day = 6;
                break;

            case "sun":
                $day = 7;
                break;
        }

        $subscriptions = $this->store->subscriptions;
        $deliveryDaySubscriptions = $subscriptions->where('delivery_day', $day);

        foreach ($deliveryDaySubscriptions as $subscription) {
            if ($subscription->status === 'active') {
                $customer = $subscription->customer;
                $emailAddress = $customer->user->email;

                /*$email = new SubscriptionCancelled([
                    'customer' => $customer,
                    'subscription' => $subscription
                ]);
                Mail::to($emailAddress)->send($email);*/

                $customer->user->sendNotification('subscription_cancelled', [
                    'customer' => $customer,
                    'subscription' => $subscription
                ]);

                $subscription->status = 'cancelled';
                $subscription->save();
                sleep(1);
            }
        }
    }

    public function getApplicationFee()
    {
        $store = $this->store;
        return $store->settings->application_fee;
    }

    public function updateStatementDescriptor()
    {
        $settings = $this->store->fresh()->settings;
        if ($settings->stripe_account) {
            \Stripe\Stripe::setApiKey(
                $settings->stripe_account['access_token']
            );

            if (!$settings->statementDescriptor) {
                $payments = \Stripe\Account::retrieve(
                    $this->store->settings->stripe_id,
                    [
                        'settings' => [
                            'payments' => []
                        ]
                    ]
                );
                $statementDescriptor = $payments['statement_descriptor'];
                $settings->statementDescriptor = $statementDescriptor;
                $settings->update();
            } else {
                $statementDescriptor = \Stripe\Account::update(
                    $settings->stripe_id,
                    [
                        'settings' => [
                            'payments' => [
                                'statement_descriptor' =>
                                    $settings->statementDescriptor
                            ]
                        ]
                    ]
                );
            }

            return $statementDescriptor;
        }
    }
}
