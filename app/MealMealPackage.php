<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MealMealPackage extends Pivot
{
    public static function substituteMeal($mealId, $subId)
    {
        $mealMealPackage = new MealMealPackage();
        $mealMealPackages = $mealMealPackage::where('meal_id', $mealId)->get();

        foreach ($mealMealPackages as $mealMealPackage) {
            $mealIds = [];
            array_push($mealIds, $mealMealPackage->meal_id);
            if (in_array($mealId, $mealIds)) {
                return 'test';
            } else {
                $mealMealPackage->update(['meal_id' => $subId]);
            }
        }
    }
}
