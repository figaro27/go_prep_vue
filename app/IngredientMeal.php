<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;

class IngredientMeal extends Pivot
{
  protected $table = 'ingredient_meal';

  protected $casts = [
    'quantity' => 'double',
    'quantity_unit' => 'string',
    'quantity_base' => 'double'
  ];

  protected $appends = [
    'quantity_base'
  ];

  public function meals() {
		return $this->hasMany('App\Meal');
	}

	public function ingredients() {
		return $this->hasMany('App\Ingredients');
  }

  public function getQuantityBaseAttribute() {
    $unitType = $this->ingredient->unit_type;

    if($unitType === 'mass') {
      $weight = new Mass($this->quantity, $this->quantity_unit);
      return $weight->toUnit('g');
    }
    elseif($unitType === 'volume') {
      $volume = new Volume($this->quantity, $this->quantity_unit);
      return $volume->toUnit('ml');
    }
    else return $this->quantity;

    
  }
}
