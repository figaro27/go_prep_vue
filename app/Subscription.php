<?php

namespace App;

use App\MealOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Subscription extends Model
{
    protected $fillable = ['status', 'cancelled_at'];

    protected $appends = ['store_name', 'latest_order', 'latest_unpaid_order', 'next_delivery_date', 'meal_ids', 'meal_quantities', 'charge_time'];

    protected $casts = [

    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer');
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

    public function getLatestUnpaidOrderAttribute()
    {
        $latestOrder = $this->orders()->where([
            ['paid', 0],
        ])
            ->whereDate('delivery_date', '>=', Carbon::now())
            ->orderBy('delivery_date', 'desc')->first();

        return $latestOrder;
    }

    public function getNextDeliveryDateAttribute()
    {
        if ($this->latest_order) {
            $date = new Carbon($this->latest_order->delivery_date->toDateTimeString());

            if ($date->isFuture()) {
                return $date;
            }
            return $date->addWeek();
        }

        // Catch all
        return $this->store->getNextDeliveryDay($this->delivery_day);
    }

    public function getMealIdsAttribute()
    {
        return $this->meals()->get()->pluck('id');
    }
    public function getMealQuantitiesAttribute()
    {
        return $this->meals()->get()->keyBy('id')->map(function ($meal) {
            return $meal->pivot->quantity ?? 0;
        });
    }

    public function getChargeTimeAttribute()
    {
        $cutoffDays = $this->store->settings->cutoff_days;
        $cutoffHours = $this->store->settings->cutoff_hours;
        $date = new Carbon($this->next_delivery_date);
        $chargeTime = $date->subDays($cutoffDays)->subHours($cutoffHours);
        return $chargeTime->format('D, m/d/Y');
    }

    /*
    public function getMealsAttribute()
    {
    if (!$this->latest_order) {
    return [];
    }

    return $this->latest_order->meals;
    }*/

    public function getStoreNameAttribute()
    {
        return $this->store->storeDetail->name;
    }

    public function isPaused()
    {
        return $this->status === 'paused';
    }

    /**
     * Renew the subscription
     *
     * @return boolean
     */
    public function renew(Collection $stripeInvoice, Collection $stripeEvent)
    {
        $latestOrder = $this->latest_unpaid_order;

        if (!$latestOrder) {
            throw new \Exception('No unpaid order for subscription #' . $this->id);
        }

        $latestOrder->paid = 1;
        $latestOrder->paid_at = new Carbon();
        $latestOrder->stripe_id = $stripeInvoice->get('id', null);
        $latestOrder->save();

        $latestOrder->events()->create([
            'type' => 'payment_succeeded',
            'stripe_event' => $stripeEvent,
        ]);

        //try {
        $newOrder = $this->latest_order->replicate(['created_at', 'updated_at', 'delivery_date', 'paid', 'paid_at', 'stripe_id']);
        $newOrder->created_at = now();
        $newOrder->updated_at = now();
        $newOrder->delivery_date = $latestOrder->delivery_date->addWeeks(1);
        $newOrder->paid = 0;
        $newOrder->viewed = 0;
        $newOrder->fulfilled = 0;
        $newOrder->order_number = substr(uniqid(rand(1, 9), false), 0, 12);
        $newOrder->push();

        try {
            $newOrder->meal_orders()->forceDelete();
        } catch (\Exception $e) {

        }

        foreach ($this->meals as $meal) {
            $mealSub = new MealOrder();
            $mealSub->order_id = $newOrder->id;
            $mealSub->store_id = $this->store->id;
            $mealSub->meal_id = $meal->id;
            $mealSub->quantity = $meal->pivot->quantity;
            $mealSub->save();
        }

        //} catch (\Exception $e) {

        //}
    }

    /**
     *  Handle payment failure
     *
     * @return boolean
     */
    public function paymentFailed(Collection $stripeInvoice, Collection $stripeEvent)
    {
        $latestOrder = $this->latest_unpaid_order;

        if (!$latestOrder) {
            throw new \Exception('No unpaid order for subscription #' . $this->id);
        }

        $latestOrder->events()->create([
            'type' => 'payment_failed',
            'stripe_event' => $stripeEvent,
        ]);
    }

    /**
     * Cancel the subscription
     *
     * @return boolean
     */
    public function cancel($withStripe = true)
    {
        if ($withStripe) {
            try {
                $subscription = \Stripe\Subscription::retrieve('sub_' . $this->stripe_id, [
                    'stripe_account' => $this->store->settings->stripe_id,
                ]);
                $subscription->cancel_at_period_end = true;
                $subscription->save();
            } catch (\Exception $e) {

            }
        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now('utc'),
        ]);

        if ($this->store->notificationEnabled('cancelled_subscription')) {
            $this->store->sendNotification('cancelled_subscription', [
                'subscription' => $this,
                'customer' => $this->customer,
            ]);
        }
    }

    /**
     * Pause the subscription
     *
     * @return boolean
     */
    public function pause($withStripe = true)
    {
        if ($withStripe) {
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
        }

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
    public function resume($withStripe = true)
    {
        if ($withStripe) {
            $subscription = \Stripe\Subscription::retrieve('sub_' . $this->stripe_id, [
                'stripe_account' => $this->store->settings->stripe_id,
            ]);
            $subscription->coupon = null;
            $subscription->save();
        }

        $this->update([
            'status' => 'active',
            'paused_at' => null,
        ]);

        if ($this->store->notificationEnabled('resumed_subscription')) {
            $this->store->sendNotification('resumed_subscription', $this);
        }
    }
}
