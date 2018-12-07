<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MealOrder extends Pivot
{
  protected $table = 'meal_orders';

  public function meal() {
		return $this->hasOne('App\Meal');
	}

	public function orders() {
		return $this->hasMany('App\Order');
	}

	//This does not work yet
// 	public function getMealNameAttribute(){
// 		foreach ($this->meal->title as $title) {
//     	return "{$title}";
//     }
// }

//     protected $appends = ['meal_name'];
}
