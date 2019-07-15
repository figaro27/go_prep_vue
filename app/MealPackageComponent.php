<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealPackageComponent extends Model
{
    public $fillable = [];
    public $casts = [];
    public $appends = [];
    public $with = ['options'];
    public $hidden = [];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function mealPackage()
    {
        return $this->belongsTo('App\MealPackage');
    }

    public function options()
    {
        return $this->hasMany('App\MealPackageComponentOption');
    }
}
