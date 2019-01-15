<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MealCategory extends Pivot
{
    public $table = 'meal_store_category';

    public $fillable = ['meal_id', 'store_category_id'];

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function storeCategory()
    {
        return $this->belongsTo('App\StoreCategory');
    }
}
