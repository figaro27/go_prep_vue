<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MealCategory extends Pivot
{
    public $table = 'meal_category';

    public $fillable = ['meal_id', 'category_id'];

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function category()
    {
        return $this->belongsTo('App\Category');
    }
}
