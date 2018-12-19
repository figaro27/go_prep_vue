<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealCategory extends Model
{
    public function meals() {
		return $this->belongsTo('App\Meal');
	}
}
