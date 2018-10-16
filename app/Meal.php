<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    public function store(){
		return $this->belongsTo('App\Store');
	}

	public function mealMeta(){
		return $this->hasMany('App\MealMeta');
	}
}
