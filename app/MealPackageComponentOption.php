<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealPackageComponentOption extends Model
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

    public function size()
    {
        return $this->belongsTo('App\MealPackageSize');
    }

    public function component()
    {
        return $this->belongsTo('App\MealPackageComponent');
    }

    public function syncMeals($meals)
    {
    }
}
