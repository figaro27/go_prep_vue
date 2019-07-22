<?php

/**
 * Meals assigned to meal package sizes
 */

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealMealPackageComponentOption extends Pivot
{
    protected $table = 'meal_meal_package_component_option';

    public $fillable = [];
    public $casts = [];
    public $appends = [];

    public function option()
    {
        return $this->belongsTo('App\MealPackageComponentOption');
    }

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function mealSize()
    {
        return $this->belongsTo('App\MealSize');
    }
}
