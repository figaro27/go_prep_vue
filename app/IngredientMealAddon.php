<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;

class IngredientMealAddon extends IngredientPivot
{
    protected $table = 'ingredient_meal_addon';

    public function addon()
    {
        return $this->belongsTo('App\MealAddon');
    }
}
