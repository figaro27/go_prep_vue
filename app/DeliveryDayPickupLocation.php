<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DeliveryDayPickupLocation extends Pivot
{
    public function delivery_day()
    {
        return $this->belongsTo('App\DeliveryDay');
    }

    public function pickup_location()
    {
        return $this->belongsTo('App\PickupLocation');
    }
}
