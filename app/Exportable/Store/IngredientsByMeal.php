<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\Utils\Data\Format;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;
use App\ReportRecord;
use Illuminate\Support\Carbon;

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
        $this->params->put('store', $this->store->details->name);
        $this->params->put('report', 'Ingredients By Meal');
        $this->params->put('date', Carbon::now()->format('m-d-Y'));

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

        $reportRecord = ReportRecord::where(
            'store_id',
            $this->store->id
        )->first();
        $reportRecord->ingredients_by_meal += 1;
        $reportRecord->update();

        return $rows;
    }

    public function exportPdfView()
    {
        return 'reports.ingredients_by_meal_pdf';
    }
}
