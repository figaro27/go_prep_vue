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

    public function getLatestOrderAttribute()
    {
        return $this->orders()->orderBy('delivery_date', 'desc')->first();
    }

    public function getNextDeliveryDateAttribute()
    {
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
            $subscription = \Stripe\Subscription::retrieve('sub_' . $this->stripe_id, [
                'stripe_account' => $this->store->settings->stripe_id,
            ]);
            $subscription->cancel_at_period_end = true;
            $subscription->save();
        } catch (\Exception $e) {
            return response()->json([], 500);
        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now('utc'),
        ]);

        if ($this->store->notificationEnabled('cancelled_subscription')) {
            $this->store->sendNotification('cancelled_subscription', $this);
        }
    }

    /**
     * Pause the subscription
     *
     * @return boolean
     */
    public function pause()
    {
        try {
            $coupon = \Stripe\Coupon::retrieve('subscription-paused', [
                'stripe_account' => $this->store->settings->stripe_id,
            ]);
        } catch (\Exception $e) {
            $coupon = \Stripe\Coupon::create([
                'duration' => 'forever',
                'id' => 'subscription-paused',
                'percent_off' => 100,
            ], [
                'stripe_account' => $this->store->settings->stripe_id,
            ]);
        }

        $storeCustomer = $this->user->getStoreCustomer($this->store->id);

        //$storeCustomer->subscriptions->retrieve('', '');

        $subscription = \Stripe\Subscription::retrieve('sub_' . $this->stripe_id, [
            'stripe_account' => $this->store->settings->stripe_id,
        ]);
        $subscription->coupon = 'subscription-paused';
        $subscription->save();

        $this->update([
            'status' => 'paused',
            'paused_at' => Carbon::now('utc'),
        ]);

        if ($this->store->notificationEnabled('paused_subscription')) {
            $this->store->sendNotification('paused_subscription', $this);
        }
    }

    /**
     * Resume the subscription
     *
     * @return boolean
     */
    public function resume()
    {
        $subscription = \Stripe\Subscription::retrieve('sub_' . $this->stripe_id, [
            'stripe_account' => $this->store->settings->stripe_id,
        ]);
        $subscription->coupon = null;
        $subscription->save();

        $this->update([
            'status' => 'active',
            'paused_at' => null,
        ]);

        if ($this->store->notificationEnabled('resumed_subscription')) {
            $this->store->sendNotification('resumed_subscription', $this);
        }
    }
}
