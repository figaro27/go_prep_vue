<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MealOrder extends Pivot
{
  protected $table = 'meal_orders';

  public function meals() {
		return $this->hasMany('App\Meal');
	}

	public function orders() {
		return $this->hasMany('App\Order');
	}
}
