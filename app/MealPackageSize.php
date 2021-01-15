<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealPackageSize extends Model
{
    use SoftDeletes;

    public $fillable = [];
    public $casts = [];
    // public $appends = ['meals'];
    public $hidden = ['store'];

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
            ->withPivot(['quantity', 'meal_size_id', 'delivery_day_id']);
    }

    // public function getMealsAttribute()
    // {
    //     return $this->meals()
    //         ->get()
    //         ->map(function ($meal) {
    //             return collect($meal)->only('id', 'meal_size_id', 'quantity');
    //         });
    // }
}
