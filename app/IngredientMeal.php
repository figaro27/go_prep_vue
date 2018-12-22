<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class IngredientMeal extends Pivot
{
  protected $table = 'ingredient_meal';

  protected $casts = [
    'quantity' => 'double',
    'quantity_unit' => 'string',
  ];

  public function meals() {
		return $this->hasMany('App\Meal');
	}

	public function ingredients() {
		return $this->hasMany('App\Ingredients');
  }
}
