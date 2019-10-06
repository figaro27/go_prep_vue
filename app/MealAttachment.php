<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealAttachment extends Model
{
    use SoftDeletes;

    protected $table = 'meal_attachments';

    public function meal()
    {
        return $this->belongsTo('meal');
    }
}
