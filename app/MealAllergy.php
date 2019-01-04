<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MealAllergy extends Pivot
{
  protected $table = 'allergy_meal';

  public function meal() {
		return $this->belongsTo('App\Meal');
	}

	public function allergy() {
		return $this->belongsTo('App\Allergy');
	}
}
