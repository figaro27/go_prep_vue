<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MealMealPackage extends Pivot
{
    public static function substituteMeal($mealId, $subId)
    {
        $mealMealPackage = new MealMealPackage();
        $mealMealPackage
            ::where('meal_id', $mealId)
            ->update(['meal_id' => $subId]);
    }
}
