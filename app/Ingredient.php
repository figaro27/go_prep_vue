<?php

namespace App;

use Auth;
use Illuminate\Database\Eloquent\Model;
use PhpUnitsOfMeasure\PhysicalQuantity\Weight;

class Ingredient extends Model
{
    public $fillable = [
        'food_name',
        'calories',
        'totalFat',
        'satFat',
        'transFat',
        'cholesterol',
        'sodium',
        'totalCarb',
        'fibers',
        'sugars',
        'proteins',
        'vitaminD',
        'potassium',
        'calcium',
        'iron',
        'addedSugars',
    ];

    const NUTRITION_FIELDS = [
        'calories',
        'totalFat',
        'satFat',
        'transFat',
        'cholesterol',
        'sodium',
        'totalCarb',
        'fibers',
        'sugars',
        'proteins',
        'vitaminD',
        'potassium',
        'calcium',
        'iron',
        'sugars',
    ];

    public function meals()
    {
        return $this->belongsToMany('App\Meal')->withPivot('quantity', 'quantity_unit')->using('App\IngredientMeal');
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public static function getIngredients()
    {
        $id = Auth::user()->id;
        $storeID = Store::where('user_id', $id)->pluck('id')->first();

        return \DB::table('ingredients')->groupBy('food_name')->select('food_name as ingredient')->selectRaw('SUM(serving_qty) as total')->get();

    }

    /**
     * Normalizes all nutritional values to 1g
     *
     * @param array|object $mealArr
     * @return void
     */
    public static function normalize($mealArr)
    {
        if (!is_array($mealArr)) {
            throw new \Exception('Invalid meal array. It should be supplied in nutritionix format.');
        }

        // We already have the nutrition for a gram weight
        if (array_key_exists('serving_weight_grams', $mealArr)) {
            $grams = $mealArr['serving_weight_grams'];
        } elseif (array_key_exists('serving_qty', $mealArr) && array_key_exists('serving_unit', $mealArr)) {
            $weight = new Weight($mealArr['serving_qty'], $mealArr['serving_unit']);
        } else {
            throw new \Exception('Unable to determine base weight for ingredient');
        }

        foreach (self::NUTRITION_FIELDS as $field) {
            if (!array_key_exists($field, $mealArr) || !is_numeric($mealArr[$field])) {
                continue;
            }

            $mealArr[$field] /= $grams;
        }

        // Clean up unneeded values
        unset($mealArr['serving_qty']);
        unset($mealArr['serving_unit']);
        unset($mealArr['serving_weight_grams']);

        return $mealArr;
    }
}
