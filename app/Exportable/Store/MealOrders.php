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
        $this->params->put(
            'show_daily_order_numbers',
            $this->store->modules->dailyOrderNumbers
        );
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
        $dailyOrderNumbersByMeal = [];
        $showDailyOrderNumbers =
            $this->store->modules && $this->store->modules->dailyOrderNumbers;
        $dates = $this->getDeliveryDates();
        $groupByDate = 'true' === $this->params->get('group_by_date', false);
        $store = $this->store;
        $params = $this->params;
        $params->date_format = $this->store->settings->date_format;
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
            &$dailyOrderNumbersByMeal,
            $groupByDate,
            &$allDates,
            $dates,
            $productionGroupId
        ) {
            $date = "";
            if ($order->delivery_date) {
                $date = $order->delivery_date->toDateString();
            }

            $dd_dates = $order->delivery_dates_array;

            /*if (!in_array($date, $allDates)) {
              $allDates[] = $date;
            }*/

            foreach ($dd_dates as $d) {
                if (!in_array($d, $allDates)) {
                    $isValid = true;

                    if (isset($dates['from'])) {
                        $from = Carbon::parse($dates['from'])->format('Y-m-d');
                        if ($d < $from) {
                            $isValid = false;
                        }
                    }

                    if (isset($dates['to'])) {
                        $to = Carbon::parse($dates['to'])->format('Y-m-d');
                        if ($d > $to) {
                            $isValid = false;
                        }
                    }

                    if ($isValid) {
                        $allDates[] = $d;
                    }
                }
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
                $size = $lineItemsOrder->getSizeAttribute();
                $title = $title . '<sep>' . $size;

                if ($groupByDate) {
                    if (!isset($lineItemQuantities[$title])) {
                        $lineItemQuantities[$title] = [];
                    }
                    if (!isset($lineItemQuantities[$title][$date])) {
                        $lineItemQuantities[$title][$date] = 0;
                    }
                    $lineItemQuantities[$title][$date] +=
                        $lineItemsOrder->quantity;
                } else {
                    if (!isset($lineItemQuantities[$title])) {
                        $lineItemQuantities[$title] = 0;
                    }

                    $lineItemQuantities[$title] += $lineItemsOrder->quantity;

                    $dailyOrderNumber =
                        $lineItemsOrder->order->dailyOrderNumber;

                    if ($dailyOrderNumber) {
                        if (
                            !array_key_exists($title, $dailyOrderNumbersByMeal)
                        ) {
                            $dailyOrderNumbersByMeal[$title] = [];
                        }

                        for ($i = 0; $i < $lineItemsOrder->quantity; $i++) {
                            $dailyOrderNumbersByMeal[
                                $title
                            ][] = $dailyOrderNumber;
                        }
                    }
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

                $newDate = $date;
                if (
                    $mealOrder->delivery_date &&
                    $mealOrder->order->isMultipleDelivery
                ) {
                    $newDate = (new Carbon($mealOrder->delivery_date))->format(
                        'Y-m-d'
                    );
                }

                $isValid = true;

                if (
                    $mealOrder->order->isMultipleDelivery &&
                    !$mealOrder->delivery_date
                ) {
                    $isValid = false;
                }

                if (isset($dates['from'])) {
                    $from = Carbon::parse($dates['from'])->format('Y-m-d');
                    if ($newDate < $from) {
                        $isValid = false;
                    }
                }

                if (isset($dates['to'])) {
                    $to = Carbon::parse($dates['to'])->format('Y-m-d');
                    if ($newDate > $to) {
                        $isValid = false;
                    }
                }

                if (!$isValid) {
                    continue;
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
                    if (!isset($mealQuantities[$title][$newDate])) {
                        $mealQuantities[$title][$newDate] = 0;
                    }
                    $mealQuantities[$title][$newDate] += $mealOrder->quantity;
                } else {
                    if (!isset($mealQuantities[$title])) {
                        $mealQuantities[$title] = 0;
                    }

                    $mealQuantities[$title] += $mealOrder->quantity;

                    $dailyOrderNumber = $mealOrder->order->dailyOrderNumber;

                    if ($dailyOrderNumber) {
                        if (
                            !array_key_exists($title, $dailyOrderNumbersByMeal)
                        ) {
                            $dailyOrderNumbersByMeal[$title] = [];
                        }

                        for ($i = 0; $i < $mealOrder->quantity; $i++) {
                            $dailyOrderNumbersByMeal[
                                $title
                            ][] = $dailyOrderNumber;
                        }
                    }
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
            foreach (
                array_merge($mealQuantities, $lineItemQuantities)
                as $title => $quantity
            ) {
                $titleParts = explode('<sep>', $title);
                $baseTitle = $titleParts[0];
                $size =
                    $titleParts && isset($titleParts[1]) ? $titleParts[1] : "";
                $row = [$quantity, $size, $baseTitle];

                if ($showDailyOrderNumbers) {
                    $numbers = array_key_exists(
                        $title,
                        $dailyOrderNumbersByMeal
                    )
                        ? $dailyOrderNumbersByMeal[$title]
                        : [];
                    $numbers = array_sort($numbers);
                    $row[] = implode(', ', $numbers);
                }

                $production->push($row);
            }
        } else {
            foreach ($mealQuantities as $title => $mealDates) {
                $temp = explode('<sep>', $title);
                $size = $temp && isset($temp[0]) ? $temp[0] : "";
                $title = $temp[1];

                $row = [];
                foreach ($allDates as $date) {
                    if (isset($mealDates[$date])) {
                        $row[] = $mealDates[$date];
                    } else {
                        $row[] = 0;
                    }
                }
                $row[] = $title;
                $row[] = $size;
                $production->push($row);
            }

            foreach ($lineItemQuantities as $title => $lineItemDates) {
                $temp = explode('<sep>', $title);
                $size = $temp && isset($temp[0]) ? $temp[0] : "";
                $title = $temp[1];

                $row = [];
                foreach ($allDates as $date) {
                    if (isset($lineItemDates[$date])) {
                        $row[] = $lineItemDates[$date];
                    } else {
                        $row[] = 0;
                    }
                }
                $row[] = $title;
                $row[] = $size;
                $production->push($row);
            }
        }

        // Removing the variations HTML from the title for CSV & XLS reports & putting it in a separate column in plain text.
        // Possibly add on special instructions if needed.
        if ($type !== 'pdf') {
            $formattedProduction = [];
            foreach ($production as $item) {
                $quantity = $item[0];
                $size = $item[1];
                $fullTitle = $item[2];
                $shortTitle = explode('<ul', $fullTitle)[0];

                preg_match_all('/14px">([^<]+)<\/li>/i', $fullTitle, $v);
                $variations = '';
                foreach ($v[1] as $v) {
                    $variations .= $v . ', ';
                }
                $variations = substr($variations, 0, -2);

                $formattedItem = [];
                array_push($formattedItem, $quantity);
                array_push($formattedItem, $size);
                array_push($formattedItem, $shortTitle);
                array_push($formattedItem, $variations);

                array_push($formattedProduction, $formattedItem);
            }

            array_unshift($formattedProduction, [
                'Orders',
                'Size',
                'Title',
                'Variations'
            ]);

            if ($groupByDate) {
                $headings = array_merge(['Title'], $this->allDates);
                $formattedProduction->prepend($headings);
            }

            return $formattedProduction;
        }

        return $production->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.meal_orders_pdf';
    }
}
