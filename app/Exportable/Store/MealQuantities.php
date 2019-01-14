<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;

class MealQuantities
{
    use Exportable;

    protected $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function exportData()
    {
        $meals = collect($this->store->getOrderMeals());

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
