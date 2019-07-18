<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealMacro extends Model
{
    public $table = 'meal_macros';

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function meal_size()
    {
        return $this->belongsTo('App\MealSize');
    }
}
