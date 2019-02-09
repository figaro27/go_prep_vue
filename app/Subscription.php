<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Subscription extends Model
{
    protected $fillable = ['status', 'cancelled_at'];

    protected $appends = ['meals', 'store_name', 'latest_order', 'next_delivery_date'];

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

    public function getLatestOrderAttribute() {
      return $this->orders()->orderBy('delivery_date', 'desc')->first();
    }

    public function getNextDeliveryDateAttribute() {
      return $this->store->getNextDeliveryDate();
    }

    public function getMealsAttribute()
    {
        if (!$this->latest_order) {
            return [];
        }

        return $this->latest_order->meals;
    }

    public function getStoreNameAttribute()
    {
        return $this->store->storeDetail->name;
    }

    /**
     * Renew the subscription
     *
     * @return boolean
     */
    public function renew(Collection $stripeInvoice)
    {
        //try {
            $newOrder = $this->latest_order->replicate(['created_at', 'updated_at', 'delivery_date']);
            $newOrder->created_at = now();
            $newOrder->updated_at = now();
            $newOrder->delivery_date = $this->next_delivery_date;
            $newOrder->order_number = substr(uniqid(rand(1, 9), false), 0, 12);
            $newOrder->push();
        //} catch (\Exception $e) {

        //}
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
        } catch (\Exception $e) {

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
