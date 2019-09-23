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
        $groupByDate = 'true' === $this->params->get('group_by_date', false);
        $params = $this->params;
        $allDates = [];

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
        $orders->map(function ($order) use (
            &$mealQuantities,
            $groupByDate,
            &$allDates,
            $productionGroupId
        ) {
            $date = $order->delivery_date->toDateString();
            if (!in_array($date, $allDates)) {
                $allDates[] = $date;
            }

            $mealOrders = $order->meal_orders()->with('meal');

            if ($productionGroupId) {
                $mealOrders = $mealOrders->whereHas('meal', function (
                    $query
                ) use ($productionGroupId) {
                    $query->where('production_group_id', $productionGroupId);
                });
            }

            foreach ($mealOrders->get() as $i => $mealOrder) {
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

                if ($groupByDate) {
                    if (!isset($mealQuantities[$title])) {
                        $mealQuantities[$title] = [];
                    }
                    if (!isset($mealQuantities[$title][$date])) {
                        $mealQuantities[$title][$date] = 0;
                    }
                    $mealQuantities[$title][$date] += $mealOrder->quantity;
                } else {
                    if (!isset($mealQuantities[$title])) {
                        $mealQuantities[$title] = 0;
                    }

                    $mealQuantities[$title] += $mealOrder->quantity;
                }
            }
        });

        sort($allDates);
        $this->allDates = array_map(function ($date) {
            return Carbon::parse($date)->format('D, m/d/y');
        }, $allDates);

        ksort($mealQuantities);

        if (!$groupByDate) {
            foreach ($mealQuantities as $title => $quantity) {
                $production->push([$title, $quantity]);
            }
        } else {
            foreach ($mealQuantities as $title => $mealDates) {
                $row = [$title];
                foreach ($allDates as $date) {
                    if (isset($mealDates[$date])) {
                        $row[] = $mealDates[$date];
                    } else {
                        $row[] = 0;
                    }
                }
                $production->push($row);
            }
        }

        if ($type !== 'pdf') {
            if (!$groupByDate) {
                $production->prepend(['Title', 'Active Orders']);
            } else {
                $headings = array_merge(['Title'], $this->allDates);
                $production->prepend($headings);
            }
        }

        return $production->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.meal_orders_pdf';
    }
}
