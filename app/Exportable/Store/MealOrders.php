<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;

class MealOrders
{
    use Exportable;

    protected $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function exportData()
    {
        return $this->store->meals->map(function($meal) {
          return [
            $meal->title,
            $meal->active_orders,
            '$'.$meal->active_orders_price
          ];
        })->prepend(['Title', 'Active Orders', 'Total Meal Price'])->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.meal_orders_pdf';
    }
}
