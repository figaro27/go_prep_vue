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
        $store = $this->store;
        $params = $this->params;
        $params->date_format = $this->store->settings->date_format;
        $allDates = [];

        $orders = $this->store->getOrders(null, $dates, true);
        $orders = $orders->where('voided', 0);

        $orders->map(function ($order) use (
            &$mealQuantities,
            &$lineItemQuantities,
            &$allDates,
            &$production,
            $dates
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

            $mealOrders = $mealOrders->get();
            $lineItemsOrders = $lineItemsOrders->get();

            // Line Items
            foreach ($lineItemsOrders as $i => $lineItemsOrder) {
                $title = $lineItemsOrder->getTitleAttribute();

                if (!isset($lineItemQuantities[$title])) {
                    $lineItemQuantities[$title] = 0;
                }

                $lineItemQuantities[$title] += $lineItemsOrder->quantity;
            }

            // Meals
            foreach ($mealOrders as $i => $mealOrder) {
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
                $title = $size . ' - ' . $title;

                if (!isset($mealQuantities[$title])) {
                    $mealQuantities[$title] = 0;
                }

                $mealQuantities[$title] += $mealOrder->quantity;
            }
            foreach ($mealOrders as $mealOrder) {
                for ($i = 1; $i <= $mealOrder->quantity; $i++) {
                    $production->push([
                        '<h1>' .
                            $mealOrder->html_title .
                            '</h1><p>' .
                            $mealOrder->meal->description .
                            '</p><p>' .
                            $mealOrder->meal->instructions .
                            '</p><p>' .
                            $mealOrder->store->details->name .
                            '</p>'
                    ]);
                }
            }
        });

        sort($allDates);
        $this->allDates = array_map(function ($date) {
            return Carbon::parse($date)->format('D, m/d/y');
        }, $allDates);

        // ksort($mealQuantities);
        // ksort($lineItemQuantities);

        foreach ($lineItemQuantities as $title => $quantity) {
            $production->push([$title]);
        }

        if ($type !== 'pdf') {
            $production->prepend(['Size', 'Title', 'Orders']);
        }

        return $production->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.labels_pdf';
    }
}
