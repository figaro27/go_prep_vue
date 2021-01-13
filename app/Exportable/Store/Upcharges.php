<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use Illuminate\Support\Carbon;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Store;

class Upcharges
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
        $dates = $this->getDeliveryDates();
        $store = $this->store;
        $params = $this->params;
        $params->date_format = $this->store->settings->date_format;
        $allDates = [];

        $orders = $this->store->getOrders(null, $dates, true);
        $orders = $orders->where('voided', 0);

        $orders->map(function ($order) use (
            &$mealQuantities,
            &$allDates,
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

            $mealOrders = $order
                ->meal_orders()
                ->where('added_price', '>', 0)
                ->with('meal')
                ->get();

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

                $title = $mealOrder->base_title;
                $size = $mealOrder->base_size;
                $upcharge = $mealOrder->added_price;
                $title = $title . '<sep>' . $size . '<sep>' . $upcharge;

                if (!isset($mealQuantities[$title])) {
                    $mealQuantities[$title] = 0;
                }

                $mealQuantities[$title] += $mealOrder->quantity;
            }
        });
        sort($allDates);
        $this->allDates = array_map(function ($date) {
            return Carbon::parse($date)->format('D, m/d/y');
        }, $allDates);

        ksort($mealQuantities);

        foreach (array_merge($mealQuantities) as $title => $quantity) {
            $titleParts = explode('<sep>', $title);
            $baseTitle = $titleParts[0];
            $size = $titleParts && isset($titleParts[1]) ? $titleParts[1] : "";
            $upcharge = $titleParts[2];

            $row = [$quantity];

            $row[] = $size;
            $row[] = $baseTitle;
            $row[] = $upcharge;

            $production->push($row);
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

            return $formattedProduction;
        }

        $productionItems = $production->toArray();

        $upcharges = [];

        foreach ($productionItems as $productionItem) {
            $productionItem[] = $productionItem[0] * $productionItem[3];
            $productionItem[3] =
                '$' . number_format((int) $productionItem[3], 2);
            $productionItem[4] =
                '$' . number_format((int) $productionItem[4], 2);
            $upcharges[] = $productionItem;
        }

        return $upcharges;
    }

    public function exportPdfView()
    {
        return 'reports.upcharges_pdf';
    }
}
