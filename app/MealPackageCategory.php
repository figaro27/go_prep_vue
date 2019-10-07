<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MealPackageCategory extends Pivot
{
    public $table = 'meal_package_category';

    public $fillable = ['meal_package_id', 'category_id'];

    public function meal_package()
    {
        return $this->belongsTo('App\MealPackage');
    }

    public function category()
    {
        return $this->belongsTo('App\Category');
    }
}
