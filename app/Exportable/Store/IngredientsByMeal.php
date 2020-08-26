<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\Utils\Data\Format;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;

class IngredientsByMeal
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
        $store = $this->store;
        $params = $this->params;
        $params->date_format = $this->store->settings->date_format;
        $ingredients = $this->store->getIngredientsByMeal($dates);
        $rows = [];
        foreach ($ingredients as $meal => $ingredient) {
            $i = count($rows);
            $array[$i] = [$meal, key($ingredient)];

            foreach ($ingredient as $food) {
                $key = key($food);
                $array[$i] += [$food[$key], $key];

                // $array[$i] += $meal;
                // $array[] = key($ingredient);
            }

            $rows = $array;
        }

        dd($rows);

        return $rows;
    }

    public function exportPdfView()
    {
        return 'reports.order_ingredients_pdf';
    }
}
