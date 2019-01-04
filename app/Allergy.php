<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Allergy extends Pivot
{
    protected $table = 'allergies';

    public function meals()
    {
        return $this->hasMany('App\Meal')->using('App\MealAllergy');
    }
}
