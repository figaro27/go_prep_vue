<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealMealPackageSelections extends Model
{
    public $fillable = [];
    public $casts = [];
    public $appends = [];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function mealPackage()
    {
        return $this->belongsTo('App\MealPackage');
    }

    public function mealPackageSize()
    {
        return $this->belongsTo('App\MealPackageSize');
    }

    public function mealPackageSelection()
    {
        return $this->belongsTo('App\MealPackageSelection');
    }

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function mealSize()
    {
        return $this->belongsTo('App\MealSize');
    }
}
