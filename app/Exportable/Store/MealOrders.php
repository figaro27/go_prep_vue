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

    public function exportAll($type = "pdf")
    {
        if (!in_array($type, ['pdf'])) {
            return null;
        }

        $this->type = $type;

        $groups = $this->store->productionGroups->toArray();

        $filename = 'public/' . md5(time()) . '.pdf';

        $vars = $this->filterVars([
            'data' => null,
            'params' => $this->params,
            'delivery_dates' => $this->getDeliveryDates(),
            'body_classes' => implode(' ', [$this->orientation]),
            'category_header' => ''
        ]);

        $pdfConfig = [
            'encoding' => 'utf-8',
            'orientation' => $this->orientation,
            'page-size' => 'Letter',
            'no-outline',
            //'margin-top' => 0,
            //'margin-bottom' => 0,
            //'margin-left' => 0,
            //'margin-right' => 0,
            //'binary' => '/usr/local/bin/wkhtmltopdf',
            'disable-smart-shrinking'
        ];

        if (config('pdf.xserver')) {
            $pdfConfig = array_merge($pdfConfig, [
                'use-xserver',
                'commandOptions' => array(
                    'enableXvfb' => true
                )
            ]);
        }

        $pdf = new Pdf($pdfConfig);

        if ($groups && count($groups) > 0) {
            foreach ($groups as $group) {
                $productionGroupId = (int) $group['id'];
                $data = $this->exportData($type, $productionGroupId);

                if (!$data || count($data) == 0) {
                    continue;
                }

                $vars['category_header'] =
                    'Production Group: ' . $group['title'];
                $vars['data'] = $data;
                $vars = $this->filterVars($vars);

                $html = view($this->exportPdfView(), $vars)->render();

                $pdf->addPage($html);
            }
        }

        $output = $pdf->toString();

        Storage::disk('local')->put($filename, $output);
        return Storage::url($filename);
    }

    public function exportData($type = null, $default_productionGroupId = 0)
    {
        $production = collect();
        $mealQuantities = [];
        $lineItemQuantities = [];
        $dates = $this->getDeliveryDates();
        $groupByDate = 'true' === $this->params->get('group_by_date', false);
        $params = $this->params;
        $allDates = [];

        $productionGroupId = $this->params->get('productionGroupId', null);
        if ($default_productionGroupId != 0) {
            $productionGroupId = $default_productionGroupId;
        }

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
        $orders = $orders->where('voided', 0);

        $orders->map(function ($order) use (
            &$mealQuantities,
            &$lineItemQuantities,
            $groupByDate,
            &$allDates,
            $productionGroupId
        ) {
            $date = $order->delivery_date->toDateString();
            if (!in_array($date, $allDates)) {
                $allDates[] = $date;
            }

            $mealOrders = $order->meal_orders()->with('meal');
            $lineItemsOrders = $order->lineItemsOrders()->with('lineItem');

            if ($productionGroupId) {
                $mealOrders = $mealOrders->whereHas('meal', function (
                    $query
                ) use ($productionGroupId) {
                    $query->where('production_group_id', $productionGroupId);
                });

                $lineItemsOrders = $lineItemsOrders->whereHas(
                    'lineItem',
                    function ($query) use ($productionGroupId) {
                        $query->where(
                            'production_group_id',
                            $productionGroupId
                        );
                    }
                );
            }

            $mealOrders = $mealOrders->get();
            $lineItemsOrders = $lineItemsOrders->get();

            // Line Items
            foreach ($lineItemsOrders as $i => $lineItemsOrder) {
                if (
                    $productionGroupId &&
                    $lineItemsOrder->lineItem->production_group_id !==
                        intval($productionGroupId)
                ) {
                    return null;
                }

                $title = $lineItemsOrder->getTitleAttribute();

                if ($groupByDate) {
                    if (!isset($lineItemQuantities[$title])) {
                        $lineItemQuantities[$title] = [];
                    }
                    if (!isset($lineItemQuantities[$title][$date])) {
                        $lineItemQuantities[$title][$date] = 1;
                    }
                    $lineItemQuantities[$title][$date] +=
                        $lineItemsOrder->quantity;
                } else {
                    if (!isset($lineItemQuantities[$title])) {
                        $lineItemQuantities[$title] = 1;
                    }

                    $lineItemQuantities[$title] += $lineItemsOrder->quantity;
                }
            }

            // Meals
            foreach ($mealOrders as $i => $mealOrder) {
                if (
                    $productionGroupId &&
                    $mealOrder->meal->production_group_id !==
                        intval($productionGroupId)
                ) {
                    return null;
                }

                /*$title =
                    $this->type !== 'pdf'
                        ? $mealOrder->getTitle()
                        : $mealOrder->html_title; */

                $title = $mealOrder->base_title;
                $size = $mealOrder->base_size;
                $title = $title . '<sep>' . $size;

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
        ksort($lineItemQuantities);

        if (!$groupByDate) {
            foreach ($mealQuantities as $title => $quantity) {
                $temp = explode('<sep>', $title);
                $title = $temp[0];
                $size = $temp && isset($temp[1]) ? $temp[1] : "";
                $production->push([$size, $title, $quantity]);
            }

            foreach ($lineItemQuantities as $title => $quantity) {
                $production->push([$title, '', $quantity]);
            }
        } else {
            foreach ($mealQuantities as $title => $mealDates) {
                $temp = explode('<sep>', $title);
                $size = $temp && isset($temp[0]) ? $temp[0] : "";
                $title = $temp[1];

                $row = [$title, $size];
                foreach ($allDates as $date) {
                    if (isset($mealDates[$date])) {
                        $row[] = $mealDates[$date];
                    } else {
                        $row[] = 0;
                    }
                }
                $production->push($row);
            }

            foreach ($lineItemQuantities as $title => $lineItemDates) {
                $row = [$title, ''];
                foreach ($allDates as $date) {
                    if (isset($lineItemDates[$date])) {
                        $row[] = $lineItemDates[$date];
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
