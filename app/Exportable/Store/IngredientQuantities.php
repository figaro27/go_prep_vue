<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\Utils\Data\Format;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;

class IngredientQuantities
{
    use Exportable;

    protected $store;

    public function __construct(Store $store, $params)
    {
        $this->store = $store;
        $this->params = $params;
    }

    public function exportData($type = null)
    {
        $dates = $this->getDeliveryDates();

        $ingredients = collect($this->store->getOrderIngredients($dates));
        $units = collect($this->store->units)->keyBy('ingredient_id');

        $data = $ingredients->map(function ($orderIngredient) use ($units) {
            $ingredient = $orderIngredient['ingredient'];

            $baseUnit = Format::baseUnit($ingredient->unit_type);
            $unit = $units->has($ingredient->id) ? $units->get($ingredient->id)->unit : $baseUnit;

            switch ($ingredient->unit_type) {
                case 'mass':
                    $mass = new Mass($orderIngredient['quantity'], $baseUnit);
                    $quantity = $mass->toUnit($unit);
                    break;

                case 'volume':
                    $volume = new Volume($orderIngredient['quantity'], $baseUnit);
                    $quantity = $volume->toUnit($unit);
                    break;

                default:
                    $quantity = $orderIngredient['quantity'];
            }

            return [
                $ingredient->food_name,
                ceil($quantity * 10) / 10, // round up to 1 decimal place
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
