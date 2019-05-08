<?php

namespace App\Http\Controllers\Store;

use App\StoreSetting;
use App\Subscription;
use App\Customer;
use App\Mail\Customer\MealPLanPaused;
use App\Mail\Customer\SubscriptionCancelled;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
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
    public function update(Request $request, StoreSetting $storeSetting)
    {
        $validatedData = $request->validate([
            'cutoff_type' => 'required|in:timed,single_day',
            'mealPlanDiscount' =>
                'required_if:applyMealPlanDiscount,true|integer|nullable|max:99',
            'deliveryFee' => 'required_if:deliveryFeeType,flat|nullable',
            'mileageBase' => 'required_if:deliveryFeeType,mileage|nullable',
            'mileagePerMile' => 'required_if:deliveryFeeType,mileage|nullable',
            'processingFee' => 'required_if:applyProcessingFee,true|nullable',
            'minimumPrice' => 'required_if:minimumOption,price',
            'minimumMeals' => 'required_if:minimumOption,meals',
            'delivery_days' => 'required|min:1',
            'meal_packages' => 'boolean'
            //'closedReason' => 'required_if:open,false'
        ]);

        $settings = StoreSetting::where('store_id', $this->store->id);

        $values = $request->except([
            'next_delivery_dates',
            'next_orderable_delivery_dates',
            'subscribed_delivery_days',
            'stripe'
        ]);
        $values['delivery_days'] = json_encode($values['delivery_days']);
        $values['delivery_distance_zipcodes'] = json_encode(
            $values['delivery_distance_zipcodes']
        );
        $values['notifications'] = json_encode($values['notifications']);

        $settings->update($values);
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

    public function pauseMealPlans(Request $request)
    {
        $settings = $this->store->settings;
        $settings->open = 0;
        $settings->closedReason = $request->closedReason;
        $settings->save();

        $subscriptions = $this->store->subscriptions;

        foreach ($subscriptions as $subscription) {
            if ($subscription->status === 'active') {
                $customer = $subscription->customer;
                $emailAddress = $customer->user->email;
                $email = new MealPLanPaused([
                    'customer' => $customer,
                    'subscription' => $subscription
                ]);
                Mail::to($emailAddress)->send($email);
                sleep(1);
            }
        }

        foreach ($subscriptions as $subscription) {
            $subscription->status = 'paused';
            $subscription->save();
        }
    }

    public function cancelMealPlans(Request $request)
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
                $email = new SubscriptionCancelled([
                    'customer' => $customer,
                    'subscription' => $subscription
                ]);
                Mail::to($emailAddress)->send($email);
                $subscription->status = 'cancelled';
                $subscription->save();
                sleep(1);
            }
        }
    }
}
