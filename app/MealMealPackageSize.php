<?php

/**
 * Meals assigned to meal package sizes
 */

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealMealPackageSize extends Pivot
{
    public $table = 'meal_meal_package_size';

    public $fillable = [];
    public $casts = [];
    public $appends = [];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function mealPackage()
    {
        return $this->belongsTo('App\MealPackage', 'meal_package_id');
    }

    public function mealPackageSize()
    {
        return $this->belongsTo('App\MealPackageSize', 'meal_package_size_id');
    }

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function mealSize()
    {
        return $this->belongsTo('App\MealSize', 'meal_size_id');
    }
}
