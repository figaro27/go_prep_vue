<?php

namespace App\Exportable\Store;

use App\Meal;
use App\Store;
use App\MealSize;
use App\ProductionGroup;
use App\LineItem;
use App\Exportable\Exportable;
use Illuminate\Support\Carbon;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Storage;

class Labels
{
    use Exportable;

    protected $store;
    protected $allDates;

    public function __construct(Store $store, $params = [])
    {
        $this->store = $store;
        $this->params = collect($params);
        $this->orientation = 'portrait';
        $this->page = $params->get('page', 1);
        $this->perPage = 10;
    }

    public function filterVars($vars)
    {
        $vars['dates'] = $this->allDates;
        return $vars;
    }

    public function exportData($type = null)
    {
        $production = collect();
        $dates = $this->getDeliveryDates();
        $store = $this->store;
        $params = $this->params;
        $params->date_format = $this->store->settings->date_format;
        $allDates = [];

        $orders = $this->store->getOrders(null, $dates, true);
        $orders = $orders->where('voided', 0);

        $total = $orders->count();
        $orders = $orders
            ->slice(($this->page - 1) * $this->perPage)
            ->take($this->perPage);
        $numDone = $this->page * $this->perPage;

        if ($numDone < $total) {
            $this->page++;
        } else {
            $this->page = null;
        }

        $orders->map(function ($order) use (&$allDates, &$production, $dates) {
            $date = "";
            if ($order->delivery_date) {
                $date = $order->delivery_date->toDateString();
            }

            $mealOrders = $order
                ->meal_orders()
                ->with('meal', 'meal.ingredients');
            $lineItemsOrders = $order->lineItemsOrders()->with('lineItem');

            $mealOrders = $mealOrders->get();
            $lineItemsOrders = $lineItemsOrders->get();

            foreach ($mealOrders as $mealOrder) {
                for ($i = 1; $i <= $mealOrder->quantity; $i++) {
                    $production->push([$mealOrder]);
                }
            }

            foreach ($lineItemsOrders as $lineItemOrder) {
                for ($i = 1; $i <= $lineItemOrder->quantity; $i++) {
                    $production->push([$lineItemOrder]);
                }
            }
        });

        $output = $production
            ->map(function ($row) {
                $row = array_map(function ($item) {
                    $meal = $item->meal;
                    $item->json = json_encode(
                        array_merge($meal->attributesToArray(), [
                            'ingredients' => $meal->ingredients->map(function (
                                $ingredient
                            ) {
                                return $ingredient->attributesToArray();
                            })
                        ])
                    );
                    return $item;
                }, $row);
                return $row;
            })
            ->toArray();

        return $output;
    }

    public function exportPdfView()
    {
        return 'reports.labels_pdf';
    }
}
