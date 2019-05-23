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

        $orders = $this->store->getOrders(null, $dates, true);
        $orders->map(function ($order) use (&$mealQuantities) {
            foreach ($order->meal_quantities as $i => $qty) {
                //$idParts = explode('-', $id);
                $meal = Meal::find($qty->meal_id);
                $title = $meal->item_title;
                if ($qty->meal_size_id) {
                    $size = MealSize::find($qty->meal_size_id);
                    if ($size) {
                        $title = $size->full_title;
                    }
                }
                if (count($qty->components)) {
                    $comp = $qty->components
                        ->map(function ($component) {
                            return $component->option;
                        })
                        ->implode(', ');
                    $title .= ' - ' . $comp;
                }
                if (!isset($mealQuantities[$title])) {
                    $mealQuantities[$title] = 0;
                }

                $mealQuantities[$title] += $qty->quantity;
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
