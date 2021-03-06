<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;

class IngredientMeal extends IngredientPivot
{
    protected $table = 'ingredient_meal';

    public function meals()
    {
        return $this->belongsTo('App\Meal');
    }
}
