<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Http\Resources\Json\JsonResource;

class MealPackageComponentOption extends Model
{
    public $fillable = [];
    public $casts = [];
    public $appends = [];
    public $with = ['meals'];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function mealPackage()
    {
        return $this->belongsTo('App\MealPackage');
    }

    public function size()
    {
        return $this->belongsTo('App\MealPackageSize');
    }

    public function component()
    {
        return $this->belongsTo('App\MealPackageComponent');
    }

    public function meals()
    {
        return $this->belongsToMany(
            'App\Meal',
            'meal_meal_package_component_option'
        )
            ->using('App\MealMealPackageSize')
            ->withPivot(['quantity', 'meal_size_id']);
    }

    public function syncMeals($meals)
    {
    }
}
