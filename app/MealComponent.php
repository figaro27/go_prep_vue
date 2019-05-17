<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealComponent extends Model
{
    use SoftDeletes;

    protected $fillable = [];

    protected $casts = [
        'price' => 'double',
        'created_at' => 'date:F d, Y'
    ];

    protected $appends = [];

    protected $hidden = [];

    protected $with = ['options'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'created_at'];

    public function meal()
    {
        return $this->belongsTo('meal');
    }

    public function store()
    {
        return $this->belongsTo('store');
    }

    public function options()
    {
        return $this->hasMany('App\MealComponentOption');
    }
}
