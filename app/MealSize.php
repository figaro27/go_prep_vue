<?php

namespace App;

use App\Meal;
use Illuminate\Database\Eloquent\Model;

class MealSize extends Model
{
    public $fillable = [];
    public $appends = ['full_title'];

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function getFullTitleAttribute()
    {
        return $this->meal->title . ' - ' . $this->title;
    }
}
