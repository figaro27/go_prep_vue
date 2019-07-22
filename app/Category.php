<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['store_id', 'category'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public $appends = [];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function meals()
    {
        return $this->belongsToMany('App\Meal')->using('App\MealCategory');
    }

    protected $casts = [];
}
