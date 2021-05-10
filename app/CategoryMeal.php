<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryMeal extends Model
{
    public $table = 'category_meal';

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function category()
    {
        return $this->belongsTo('App\Category');
    }
}
