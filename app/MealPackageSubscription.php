<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealPackageSubscription extends Pivot
{
    protected $table = 'meal_package_subscriptions';

    protected $casts = [
        'delivery_date' => 'datetime:Y-m-d'
    ];

    protected $appends = ['hasCustomName', 'full_title'];

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

    public function getHasCustomNameAttribute()
    {
        if ($this->customTitle || $this->customSize) {
            return true;
        } else {
            return false;
        }
    }

    public function getFullTitleAttribute()
    {
        $title = $this->customTitle
            ? $this->customTitle
            : $this->meal_package->title;
        $size = $this->meal_package_size
            ? ' - ' . $this->meal_package_size->title
            : null;
        if ($this->customSize) {
            $size = ' - ' . $this->customSize;
        }

        return $title . $size;
    }
}
