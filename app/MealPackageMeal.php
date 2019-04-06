<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealPackageMeal extends Pivot
{
    protected $table = 'meal_meal_package';

    protected $appends = [];

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function package()
    {
        return $this->belongsTo('App\MealPackage');
    }
}
