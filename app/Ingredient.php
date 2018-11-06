<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{

	public function meal(){
		return $this->belongsTo('App\Meal');
	}

    public function store(){
		return $this->belongsTo('App\Store');
	}
}
