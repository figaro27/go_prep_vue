<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Allergy extends Pivot
{
    protected $table = 'allergies';

    protected $hidden = ['created_at', 'updated_at'];

    public function meals()
    {
        return $this->hasMany('App\Meal')->using('App\MealAllergy');
    }
}
