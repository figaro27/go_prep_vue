<?php

namespace App;

use App\Meal;
use Illuminate\Database\Eloquent\Model;

class MealSize extends Model
{
    public $fillable = [];
    public $appends = [];

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }
}
