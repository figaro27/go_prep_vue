<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealPackageSelections extends Model
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
}
