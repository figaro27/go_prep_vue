<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealTag extends Model
{
  public $fillable = ['tag', 'slug', 'store_id'];

  public function meals() {
		return $this->belongsToMany('App\Meal', 'meal_meal_tag');
	}
}
