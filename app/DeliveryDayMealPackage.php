<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryDayMealPackage extends Model
{
    public function meal_package()
    {
        return $this->hasOne(
            'App\OptimizedMealPackage',
            'id',
            'meal_package_id'
        );
    }
}
