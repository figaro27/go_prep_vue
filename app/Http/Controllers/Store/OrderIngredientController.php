<?php

namespace App\Http\Controllers\Store;

use App\Utils\Data\ExportsData;
use App\Utils\Data\Format;

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

        $ingredients = $ingredients->map(function ($item, $id) {
            return [
                'id' => $id,
                'quantity' => $item['quantity'],
            ];
        })->toArray();

        return $ingredients;
    }

    public function exportData($type)
    {
        $ingredients = collect($this->store->getOrderIngredients());
        $units = collect($this->store->units);

        $data = $ingredients->map(function ($orderIngredient) use ($units) {
            $ingredient = $orderIngredient['ingredient'];
            return [
                $ingredient->food_name,
                ceil($orderIngredient['quantity']),
                $units->has($ingredient->id) ? $units->get($ingredient->id)->unit : Format::baseUnit($ingredient->unit_type),
            ];
        });

        $data = array_merge([
            [
                'Ingredient', 'Quantity', 'Unit',
            ],
        ], $data->toArray());
        return $data;
    }

    public function exportPdfView()
    {
        return 'reports.order_ingredients_pdf';
    }
}
