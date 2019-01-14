<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\Utils\Data\Format;

class IngredientQuantities
{
    use Exportable;

    protected $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function exportData()
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
