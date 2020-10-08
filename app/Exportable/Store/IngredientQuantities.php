<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\Utils\Data\Format;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;
use App\ReportRecord;

class IngredientQuantities
{
    use Exportable;

    protected $store;

    public function __construct(Store $store, $params)
    {
        $this->store = $store;
        $this->params = $params;
        $this->orientation = 'portrait';
    }

    public function exportData($type = null)
    {
        $dates = $this->getDeliveryDates();

        $ingredients = collect($this->store->getOrderIngredients($dates));
        $units = collect($this->store->units)->keyBy('ingredient_id');
        $store = $this->store;
        $params = $this->params;
        $params->date_format = $this->store->settings->date_format;
        $data = [];

        $ingredients->map(function ($orderIngredient) use ($units, &$data) {
            $ingredient = $orderIngredient['ingredient'];

            $baseUnit = Format::baseUnit($ingredient->unit_type);
            $unit = $units->has($ingredient->id)
                ? $units->get($ingredient->id)->unit
                : $baseUnit;

            switch ($ingredient->unit_type) {
                case 'mass':
                    $mass = new Mass($orderIngredient['quantity'], $baseUnit);
                    $quantity = $mass->toUnit($unit);
                    break;

                case 'volume':
                    $volume = new Volume(
                        $orderIngredient['quantity'],
                        $baseUnit
                    );

                    if ($unit == 'fl-oz' || $unit == 'fl oz') {
                        $unit = 'fl. oz.';
                        $quantity = $orderIngredient['quantity'];
                    } elseif ($unit == 'pnt') {
                        $unit = 'pt';
                        $quantity = $orderIngredient['quantity'];
                    } else {
                        $quantity = $volume->toUnit($unit);
                    }
                    break;

                default:
                    $quantity = $orderIngredient['quantity'];
            }

            $data[] = [
                $ingredient->food_name,
                ceil($quantity * 10) / 10, // round up to 1 decimal place
                $unit
            ];
        });

        $data = collect($data);
        $data = $data->sortBy('0');

        $data = array_merge(
            [['Ingredient', 'Quantity', 'Unit']],
            $data->toArray()
        );

        $reportRecord = ReportRecord::where(
            'store_id',
            $this->store->id
        )->first();
        $reportRecord->ingredients_production += 1;
        $reportRecord->update();

        return $data;
    }

    public function exportPdfView()
    {
        return 'reports.order_ingredients_pdf';
    }
}
