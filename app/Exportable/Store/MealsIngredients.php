<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use App\Meal;

class MealsIngredients
{
    use Exportable;

    protected $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function exportData($type = null)
    {
        $meals = Meal::with('ingredients')->get();
        $menu = $meals->map(function($meal) {
            $ingredientsName = $meal->ingredients->pluck('food_name');
            $ingredientsQuantity = $meal->ingredients->pluck('quantity');
            $ingredientsUnit = $meal->ingredients->pluck('quantity_unit');
          return [
            $meal->title,
            $ingredientsName,
            $ingredientsQuantity,
            $ingredientsUnit
          ];
        });
        
        return $menu->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.meals_ingredients_pdf';
    }
}
