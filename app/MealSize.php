<?php

namespace App;

use App\Meal;
use Illuminate\Database\Eloquent\Model;
use App\MealSubscription;
use App\MealMealPackage;
use App\MealMealPackageSize;
use App\MealMealPackageComponentOption;
use App\MealMealPackageAddon;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils\Data\Format;

class MealSize extends Model
{
    use SoftDeletes;

    public $fillable = ['store_id'];
    public $hidden = ['meal'];
    protected $with = ['ingredients'];

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function componentOptions()
    {
        return $this->hasMany('App\MealComponentOption');
    }

    public function addons()
    {
        return $this->hasMany('App\MealAddon');
    }

    public function ingredients()
    {
        return $this->belongsToMany('App\Ingredient')
            ->withPivot('quantity', 'quantity_unit', 'quantity_unit_display')
            ->using('App\IngredientMealSize');
    }

    public function syncIngredients($rawIngredients)
    {
        $ingredients = collect();

        foreach ($rawIngredients as $ingredientArr) {
            $ingredient = Ingredient::fromNutritionix(
                $this->meal_id,
                $ingredientArr
            );

            if ($ingredient) {
                $ingredients->push($ingredient);
            }
        }

        $syncIngredients = $ingredients->mapWithKeys(function ($val, $key) use (
            $rawIngredients
        ) {
            return [
                $val->id => [
                    'quantity' => $rawIngredients[$key]['quantity'] ?? 1,
                    'quantity_unit' =>
                        $rawIngredients[$key]['quantity_unit'] ??
                        Format::baseUnit($val->unit_type),
                    'quantity_unit_display' =>
                        $rawIngredients[$key]['quantity_unit_display'] ??
                        Format::baseUnit($val->unit_type)
                ]
            ];
        });

        $this->ingredients()->sync($syncIngredients);
    }
}
