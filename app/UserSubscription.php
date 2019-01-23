<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserSubscription extends Model
{
    protected $fillable = ['status', 'cancelled_at'];

    protected $appends = ['meals'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
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
