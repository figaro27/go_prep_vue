<?php

namespace App\Http\Controllers\Store;

use App\StoreSetting;
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
            'mealPlanDiscount' => 'required_if:applyMealPlanDiscount,true|integer|nullable|max:99',
            'deliveryFee' => 'required_if:applyDeliveryFee,true|nullable',
            'processingFee' => 'required_if:applyProcessingFee,true|nullable',
            'minimumPrice' => 'required_if:minimumOption,price',
            'minimumMeals' => 'required_if:minimumOption,meals',
            'delivery_days' => 'required|min:1',
            //'closedReason' => 'required_if:open,false'
        ]);

        $settings = StoreSetting::where('store_id', $this->store->id);

        $values = $request->except(['next_delivery_dates', 'next_orderable_delivery_dates', 'stripe']);
        $values['delivery_days'] = json_encode($values['delivery_days']);
        $values['delivery_distance_zipcodes'] = json_encode($values['delivery_distance_zipcodes']);
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
    public function createStripeAccount(Request $request) {
      
      if($this->store->hasStripe()) {
        return;
      }
      
      $settings = $this->store->settings;

      $account = Stripe\Account::create([
        "type" => "standard",
        "country" => "US",
        "email" => $this->store->user->userDetail->email,
      ]);

      if(!isset($account->id)) {
        return null;
      }
      
    }

    public function pauseMealPlans()
    {

        $settings = $this->store->settings;
        $settings->open = 0;
        $settings->save();

        $subscriptions = $this->store->subscriptions;

        foreach ($subscriptions as $subscription){
            $subscription->status = 'paused';
            $subscription->save();
            $customer = $subscription->user;
            $subscription->user->sendNotification('meal_plan_paused', compact([$subscription, $customer]));
    }
}
}
