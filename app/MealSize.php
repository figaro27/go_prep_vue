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

    public $fillable = [];
    public $appends = ['full_title', 'activeSubscriptionsOrPackage'];
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

    public function getActiveSubscriptionsOrPackageAttribute()
    {
        $mealSubs = MealSubscription::where('meal_size_id', $this->id)
            ->whereHas('subscription', function ($sub) {
                $sub->where('status', '=', 'active');
            })
            ->count();

        $mealMealPackages = MealMealPackage::where(
            'meal_size_id',
            $this->id
        )->count();

        $mealMealPackageSizes = MealMealPackageSize::where(
            'meal_size_id',
            $this->id
        )->count();

        $mealMealPackageComponentOptions = MealMealPackageComponentOption::where(
            'meal_size_id',
            $this->id
        )->count();

        $mealMealPackageAddons = MealMealPackageAddon::where(
            'meal_size_id',
            $this->id
        )->count();

        if (
            $mealSubs > 0 ||
            $mealMealPackages > 0 ||
            $mealMealPackageSizes > 0 ||
            $mealMealPackageComponentOptions ||
            $mealMealPackageAddons
        ) {
            return true;
        } else {
            return false;
        }
    }
}
