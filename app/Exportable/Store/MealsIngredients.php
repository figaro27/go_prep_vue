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

    public function __construct(Store $store, $params = [])
    {
        $this->store = $store;
        $this->orientation = 'portrait';
    }

    public function exportData($type = null)
    {
        $meals = Meal::with('ingredients')
            ->where('store_id', $this->store->id)
            ->get();

        $rows = [];

        foreach ($meals as $meal) {
            $i = count($rows);

            if (count($meal->ingredients) > 0) {
                $rows[$i] = [
                    $meal->title,
                    $meal->ingredients[0]->food_name,
                    $meal->ingredients[0]->quantity,
                    $meal->ingredients[0]->quantity_unit
                ];
            }

            if (count($meal->ingredients) > 1) {
                for ($x = 1; $x < count($meal->ingredients); $x++) {
                    $ingredient = $meal->ingredients[$x];

                    $rows[] = [
                        '',
                        $ingredient->food_name,
                        $ingredient->quantity,
                        $ingredient->quantity_unit
                    ];
                }
            }
        }

        return $rows;
    }

    public function exportPdfView()
    {
        return 'reports.meals_ingredients_pdf';
    }
}
