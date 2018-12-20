<?php

namespace App;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    public $fillable = [
        'food_name',
        'serving_qty',
        'serving_unit',
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

    public function meal()
    {
        return $this->belongsTo('App\Meal');
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
}
