<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;

class IngredientMealComponentOption extends IngredientPivot
{
    protected $table = 'ingredient_meal_component_option';

    public function component()
    {
        return $this->belongsTo('App\MealComponentOption');
    }
}
