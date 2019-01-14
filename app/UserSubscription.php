<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function cancel() {
      // todo: implement stripe logic

      if($this->store->notificationEnabled('cancelled_subscription')) {
        $this->store->sendNotification('cancelled_subscription', $this);
      }
    }
}
