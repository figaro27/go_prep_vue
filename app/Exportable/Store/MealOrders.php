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
        $this->orientation = 'portrait';
    }

    public function exportData($type = null)
    {
        $production = collect();
        $mealQuantities = [];
        $dates = $this->getDeliveryDates();

        $params = $this->params;

        $orders = $this->store->getOrders(null, $dates, true);
        $orders->map(function ($order) use (&$mealQuantities) {
            $productionGroupId = $this->params->get('productionGroupId');
            foreach ($order->meal_orders()->get() as $i => $mealOrder) {
                if (
                    $mealOrder->meal->production_group_id !== $productionGroupId
                ) {
                    return null;
                }
                $title =
                    $this->type !== 'pdf'
                        ? $mealOrder->title
                        : $mealOrder->html_title;

                if (!isset($mealQuantities[$title])) {
                    $mealQuantities[$title] = 0;
                }

                $mealQuantities[$title] += $mealOrder->quantity;
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
