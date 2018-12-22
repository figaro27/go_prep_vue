<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

use PhpUnitsOfMeasure\PhysicalQuantity\Mass;

class IngredientMeal extends Pivot
{
  protected $table = 'ingredient_meal';

  protected $casts = [
    'quantity' => 'double',
    'quantity_unit' => 'string',
    'quantity_grams' => 'double'
  ];

  protected $appends = [
    'quantity_grams'
  ];

  public function meals() {
		return $this->hasMany('App\Meal');
	}

	public function ingredients() {
		return $this->hasMany('App\Ingredients');
  }

  public function getQuantityGramsAttribute() {
    $weight = new Mass($this->quantity, $this->quantity_unit);
    return $weight->toUnit('g');
  }
}
