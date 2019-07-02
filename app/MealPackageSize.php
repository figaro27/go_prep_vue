<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealPackageSize extends Model
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
        return $this->belongsTo('App\MealPackage', 'meal_package_id');
    }

    public function meals()
    {
        return $this->belongsToMany('App\Meal', 'meal_meal_package_size')
            ->using('App\MealMealPackageSize')
            ->withPivot(['quantity', 'meal_size_id']);
    }
}
