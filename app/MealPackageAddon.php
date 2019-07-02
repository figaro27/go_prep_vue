<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealPackageAddon extends Model
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

    public function meals()
    {
        return $this->belongsToMany('App\Meal', 'meal_meal_package_addon')
            ->using('App\MealMealPackageSize')
            ->withPivot(['quantity', 'meal_size_id']);
    }
}
