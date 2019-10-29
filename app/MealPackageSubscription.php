<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealPackageSubscription extends Pivot
{
    protected $table = 'meal_package_subscriptions';

    protected $appends = [];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function orders()
    {
        return $this->belongsTo('App\Order');
    }

    public function meal_package()
    {
        return $this->belongsTo('App\MealPackage');
    }

    public function meal_package_size()
    {
        return $this->belongsTo('App\MealPackageSize');
    }
}
