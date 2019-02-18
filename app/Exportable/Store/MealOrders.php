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

    public function exportData($type = null)
    {
        $production = $this->store->meals->map(function($meal) {
          return [
            $meal->title,
            $meal->active_orders,
            // '$'.$meal->active_orders_price
          ];
        });

        if($type !== 'pdf'){
            $production->prepend(['Title', 'Active Orders']);
        }

        return $production->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.meal_orders_pdf';
    }
}
