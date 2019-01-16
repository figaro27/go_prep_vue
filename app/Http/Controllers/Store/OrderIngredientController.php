<?php

namespace App\Http\Controllers\Store;

use App\Utils\Data\ExportsData;
use App\Utils\Data\Format;
use App\Unit;

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
        $orderIngredients = collect($this->store->getOrderIngredients());
        $units = collect($this->store->units)->keyBy('ingredient_id');
        $ingredients = $this->store->ingredients->keyBy('id');

        $data = $orderIngredients->map(function ($orderIngredient) use ($units, $ingredients) {
            $ingredient = $ingredients->get($orderIngredient['id']);
            if ($units->has($ingredient->id)) {
                $unit = $units->get($ingredient->id)->unit;
                $quantity = Unit::convert(
                  $orderIngredient['quantity'],
                  Format::baseUnit($ingredient->unit_type),
                  $unit
                );
            } else {
                $unit = Format::baseUnit($ingredient->unit_type);
                $quantity = ceil($orderIngredient['quantity']);
            }

            return [
                $ingredient->food_name,
                ceil($quantity),
                $unit,
            ];
        })->sortBy('0');

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
