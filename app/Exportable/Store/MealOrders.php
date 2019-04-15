<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\Meal;
use App\MealSize;

class MealOrders
{
    use Exportable;

    protected $store;

    public function __construct(Store $store, $params = [])
    {
        $this->store = $store;
        $this->params = $params;
    }

    public function exportData($type = null)
    {
        $production = collect();
        $mealQuantities = [];
        $dates = $this->getDeliveryDates();

        $orders = $this->store->getOrders(null, $dates, true);
        $orders->map(function ($order) use (&$mealQuantities) {
            foreach ($order->meal_quantities as $id => $qty) {
                $idParts = explode('-', $id);
                $meal = Meal::find($idParts[0]);
                $title = $meal->item_title;
                if (isset($idParts[1])) {
                    $size = MealSize::find($idParts[1]);
                    if ($size) {
                        $title = $size->full_title;
                    }
                }
                if (!isset($mealQuantities[$title])) {
                    $mealQuantities[$title] = 0;
                }

                $mealQuantities[$title] += $qty;
            }
        });

        ksort($mealQuantities);

        foreach ($mealQuantities as $title => $quantity) {
            $production->push([$title, $quantity]);
        }

        if ($type !== 'pdf') {
            $production->prepend(['Title', 'Active Orders']);
        }

        return $production->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.meal_orders_pdf';
    }
}
