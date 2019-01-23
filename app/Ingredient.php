<?php

namespace App;

use Auth;
use Illuminate\Database\Eloquent\Model;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;

class Ingredient extends Model
{
    public $fillable = [
        'food_name',
        'unit_type',
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
        'image',
        'image_thumb',
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

    public $appends = ['quantity', 'quantity_unit'];

    public function getQuantityAttribute() {
      if($this->pivot && $this->pivot->quantity) {
        return $this->pivot->quantity;
      }
      else return null;
    }

    public function getQuantityUnitAttribute() {
      if($this->pivot && $this->pivot->quantity_unit) {
        return $this->pivot->quantity_unit;
      }
      else return null;
    }

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
     * Normalizes all nutritional values to a base unit of measure
     *
     * @param array|object $mealArr
     * @return void
     */
    public static function normalize($mealArr)
    {
        if (!is_array($mealArr)) {
            throw new \Exception('Invalid meal array. It should be supplied in nutritionix format.');
        }
        if (!array_key_exists('serving_qty', $mealArr)) {
            throw new \Exception('No serving quantity provided.');
        }
        if (!array_key_exists('serving_unit', $mealArr)) {
            throw new \Exception('No serving unit provided.');
        }

        // Find unit type
        $mealArr['unit_type'] = Unit::getType($mealArr['serving_unit']);

        if ($mealArr['unit_type'] === 'mass') {
            // We already have the nutrition for a gram weight
            if (array_key_exists('serving_weight_grams', $mealArr)) {
                $unitFactor = $mealArr['serving_weight_grams'];
            } elseif (array_key_exists('serving_qty', $mealArr) && array_key_exists('serving_unit', $mealArr)) {
                $weight = new Mass($mealArr['serving_qty'], $mealArr['serving_unit']);
                $unitFactor = $weight->toUnit('g');
            } else {
                throw new \Exception('Unable to determine base weight for ingredient');
            }
        } elseif ($mealArr['unit_type'] === 'volume') {
            if (array_key_exists('serving_qty', $mealArr) && array_key_exists('serving_unit', $mealArr)) {
                $volume = new Volume($mealArr['serving_qty'], $mealArr['serving_unit']);
                $unitFactor = $volume->toUnit('ml');
            } else {
                throw new \Exception('Unable to determine base volume for ingredient');
            }
        } else {
            $unitFactor = 1;
        }

        foreach (self::NUTRITION_FIELDS as $field) {
            if (!array_key_exists($field, $mealArr) || !is_numeric($mealArr[$field])) {
                continue;
            }

            // We already do this in the FE
            //$mealArr[$field] /= $unitFactor;
        }

        // Thumbnail
        if (array_key_exists('photo', $mealArr)) {
            $photo = $mealArr['photo']['highres'] ?? $mealArr['photo']['thumb'];
            $photoThumb = $mealArr['photo']['thumb'] ?? $photo;
            $mealArr['image'] = $photo;
            $mealArr['image_thumb'] = $photoThumb;
        }

        // Clean up unneeded values
        unset($mealArr['serving_qty']);
        unset($mealArr['serving_unit']);
        unset($mealArr['serving_weight_grams']);
        unset($mealArr['photo']);

        return $mealArr;
    }
}
