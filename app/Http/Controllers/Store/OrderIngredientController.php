<?php

namespace App\Http\Controllers\Store;

use App\Utils\Data\ExportsData;
use App\Ingredient;

class OrderIngredientController extends StoreController
{
    use ExportsData;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ingredients = collect($this->store->getOrderIngredients());

        $ingredients = $ingredients->map(function ($quantity, $id) {
            return [
                'id' => $id,
                'quantity' => $quantity
            ];
        })->toArray();

        return $ingredients;
    }

    public function exportData($type)
    {
        $data = collect($this->index())->map(function($orderIngredient) {
          return [
            $orderIngredient->ingredient->food_name,
            $orderIngredient->quantity,
            $orderIngredient->ingredient->food_name,
          ];
        });

        $data = array_merge([
            [
                'Ingredient', 'Quantity', 'Unit',
            ],
        ], $data);
        return $data;
    }
}
