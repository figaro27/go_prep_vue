<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;

class MealQuantities
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
        $meals = collect($this->store->getOrderMeals($dates));

        $meals = $meals->map(function ($item, $id) {
            return [
                'id' => $id,
                'meal' => $item['meal']->title,
                'quantity' => $item['quantity'],
            ];
        })->toArray();

        return $meals;
    }

    public function exportPdfView()
    {
        return 'reports.meal_quantities_pdf';
    }
}
