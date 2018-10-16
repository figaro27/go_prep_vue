<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealOrder extends Model
{
    public function meal(){
		return $this->hasMany('App\Meal');
	}

	public function order(){
		return $this->hasMany('App\Order')
	}
}
