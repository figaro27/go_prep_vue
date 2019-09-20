<?php

namespace App\Exportable\Store;

use App\Meal;
use App\Store;
use App\MealSize;
use App\ProductionGroup;
use App\Exportable\Exportable;
use Illuminate\Support\Carbon;

class MealOrders
{
    use Exportable;

    protected $store;
    protected $allDates;

    public function __construct(Store $store, $params = [])
    {
        $this->store = $store;
        $this->params = collect($params);
        if (!$this->params->has('group_by_date')) {
            $this->params->put('group_by_date', false);
        }
        $this->orientation = 'portrait';
    }

    public function filterVars($vars)
    {
        $vars['dates'] = $this->allDates;
        return $vars;
    }

    public function exportData($type = null)
    {
        $production = collect();
        $mealQuantities = [];
        $dates = $this->getDeliveryDates();

        $params = $this->params;

        $productionGroupId = $this->params->get('productionGroupId', null);
        if ($productionGroupId != null) {
            $productionGroupTitle = ProductionGroup::where(
                'id',
                $productionGroupId
            )->first()->title;
            $params->productionGroupTitle = $productionGroupTitle;
        } else {
            $params->productionGroupTitle = null;
        }

        $orders = $this->store->getOrders(null, $dates, true);
        $orders->map(function ($order) use (&$mealQuantities) {
            $productionGroupId = $this->params->get('productionGroupId', null);
            foreach (
                $order
                    ->meal_orders()
                    ->with('meal')
                    ->get()
                as $i => $mealOrder
            ) {
                if (
                    $productionGroupId &&
                    $mealOrder->meal->production_group_id !==
                        intval($productionGroupId)
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
