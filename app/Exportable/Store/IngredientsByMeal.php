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
            $subArray = [];

            $i = 0;

            foreach ($ingredient as $ingredientName => $food) {
                $array = [];

                if ($i == 0) {
                    array_push($array, $meal);
                } else {
                    array_push($array, "");
                }

                array_push(
                    $array,
                    $ingredientName,
                    $food[key($food)],
                    key($food)
                );

                $subArray[$i] = $array;

                $i++;
            }

            array_push($rows, ...$subArray);
        }

        return $rows;
    }

    public function exportPdfView()
    {
        return 'reports.ingredients_by_meal_pdf';
    }
}
