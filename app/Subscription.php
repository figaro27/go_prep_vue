<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    protected $fillable = ['status', 'cancelled_at'];

    protected $appends = ['meals', 'store_name'];

    protected $casts = [
        'created_at' => 'date:F d, Y',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function meals()
    {
        return $this->belongsToMany('App\Meal', 'meal_subscriptions')->withPivot('quantity')->withTrashed();
    }

    public function meal_subscriptions()
    {
        return $this->hasMany('App\MealSubscription');
    }

    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    public function getMealsAttribute()
    {
        $latestOrder = $this->orders()->orderBy('delivery_date', 'desc')->first();

        if (!$latestOrder) {
            return [];
        }

        return $latestOrder->meals;
    }

    
    public function getStoreNameAttribute(){
        return $this->store->storeDetail->name;
    }

    /**
     * Cancel the subscription
     *
     * @return boolean
     */
    public function cancel()
    {
        try {
          $subscription = \Stripe\Subscription::retrieve($this->stripe_id);
          $subscription->cancel_at_period_end = true;
          $subscription->save();
        }
        catch(\Exception $e) {

        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now(),
        ]);

        if ($this->store->notificationEnabled('cancelled_subscription')) {
            $this->store->sendNotification('cancelled_subscription', $this);
        }
    }
}
