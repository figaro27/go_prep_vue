<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealAddon extends Model
{
    use SoftDeletes;

    protected $table = 'meal_addons';

    protected $fillable = [];

    protected $casts = [
        'price' => 'double',
        'created_at' => 'date:F d, Y'
    ];

    protected $appends = [];

    protected $hidden = [];

    protected $with = [];

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

    public function ingredients()
    {
        return $this->belongsToMany('App\Ingredient')
            ->withPivot('quantity', 'quantity_unit', 'quantity_unit_display')
            ->using('App\IngredientMealAddon');
    }
}
