<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;

class IngredientMealSize extends IngredientPivot
{
    protected $table = 'ingredient_meal_size';

    public function size()
    {
        return $this->belongsTo('App\MealSize');
    }
}
