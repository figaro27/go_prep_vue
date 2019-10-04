<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealMealAttachment extends Model
{
    use SoftDeletes;

    protected $table = 'meal_meal_attachments';

    public function meal()
    {
        return $this->belongsTo('meal');
    }
}
