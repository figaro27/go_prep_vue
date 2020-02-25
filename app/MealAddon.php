<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Ingredient;
use App\MealSubscriptionAddon;

class MealAddon extends Model
{
    use SoftDeletes;

    protected $table = 'meal_addons';

    protected $fillable = [];

    protected $casts = [
        'price' => 'double',
        'created_at' => 'date:F d, Y'
    ];

    protected $appends = ['activeSubscriptions'];

    protected $hidden = [];

    protected $with = ['ingredients'];

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

    public function getActiveSubscriptionsAttribute()
    {
        $mealSubs = MealSubscriptionAddon::where('meal_addon_id', $this->id)
            ->whereHas('mealSubscription', function ($mealSub) {
                $mealSub->whereHas('subscription', function ($sub) {
                    $sub->where('status', '=', 'active');
                });
            })
            ->count();

        if ($mealSubs > 0) {
            return true;
        } else {
            return false;
        }
    }
}
