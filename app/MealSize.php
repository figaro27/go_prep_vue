<?php

namespace App;

use App\Meal;
use Illuminate\Database\Eloquent\Model;

class MealSize extends Model
{
    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }
}
