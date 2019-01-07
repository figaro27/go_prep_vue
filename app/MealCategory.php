<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealCategory extends Model
{
    public $fillable = ['meal_id', 'category'];

    public function meals()
    {
        return $this->belongsTo('App\Meal');
    }
}
