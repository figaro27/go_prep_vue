<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['store_id', 'category', 'subtitle'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public $appends = [];

    protected $casts = [
        'date_range' => 'boolean',
        'date_range_exclusive' => 'boolean',
        'active' => 'boolean',
        'activeForStore' => 'boolean',
        'minimumOnlyIfCategoryAdded' => 'boolean'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function meals()
    {
        return $this->belongsToMany('App\Meal')->using('App\MealCategory');
    }
}
