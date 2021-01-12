<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealPackageOrder extends Pivot
{
    protected $table = 'meal_package_orders';

    protected $appends = ['hasCustomName', 'full_title', 'itemsCount'];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function orders()
    {
        return $this->belongsTo('App\Order');
    }

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public function meal_package()
    {
        return $this->belongsTo('App\MealPackage')->withTrashed();
    }

    public function meal_package_size()
    {
        return $this->belongsTo('App\MealPackageSize');
    }

    public function meal_orders()
    {
        return $this->hasMany('App\MealOrder', 'meal_package_order_id', 'id');
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

    public function getItemsCountAttribute()
    {
        $count = 0;
        foreach ($this->meal_orders as $mealOrder) {
            $count += $mealOrder->quantity;
        }
        return $count;
    }
}
