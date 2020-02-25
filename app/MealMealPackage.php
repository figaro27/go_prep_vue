<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MealMealPackage extends Pivot
{
    public function meal_package()
    {
        return $this->belongsTo('App\MealPackage');
    }

    public static function substituteMeal($mealId, $subId)
    {
        // Not being used
        $mealMealPackage = new MealMealPackage();
        $mealMealPackages = $mealMealPackage::where('meal_id', $mealId)->get();
    }
}
