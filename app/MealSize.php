<?php

namespace App;

use App\Meal;
use Illuminate\Database\Eloquent\Model;

class MealSize extends Model
{
    public $fillable = [];
    public $appends = ['full_title'];
    public $hidden = ['meal'];
    protected $with = ['ingredients'];

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function ingredients()
    {
        return $this->belongsToMany('App\Ingredient')
            ->withPivot('quantity', 'quantity_unit', 'quantity_unit_display')
            ->using('App\IngredientMealSize');
    }

    public function getFullTitleAttribute()
    {
        if (isset($this->meal) && $this->meal != null) {
            return $this->meal->title . ' - ' . $this->title;
        } else {
            return "";
        }
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
