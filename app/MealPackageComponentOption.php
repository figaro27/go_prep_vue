<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Http\Resources\Json\JsonResource;

class MealPackageComponentOption extends Model
{
    public $fillable = [];
    public $casts = [];
    public $appends = [];
    public $with = [];
    public $hidden = ['created_at', 'updated_at'];

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $arr = parent::toArray();

        $arr['meals'] = $this->meals->map(function ($meal) {
            return [
                'meal_id' => $meal->id,
                'quantity' => $meal->quantity,
                'meal_size_id' => $meal->pivot->meal_size_id,
                'meal_size_id' => $meal->pivot->price
            ];
        });

        return $arr;
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function mealPackage()
    {
        return $this->belongsTo('App\MealPackage');
    }

    public function size()
    {
        return $this->belongsTo('App\MealPackageSize');
    }

    public function component()
    {
        return $this->belongsTo('App\MealPackageComponent');
    }

    public function meals()
    {
        return $this->belongsToMany(
            'App\Meal',
            'meal_meal_package_component_option'
        )
            ->using('App\MealMealPackageSize')
            ->withPivot(['quantity', 'meal_size_id', 'price']);
    }

    public function syncMeals($meals)
    {
    }
}
