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
use Illuminate\Support\Facades\Log;

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
        $vars['body_classes'] = implode(' ', [$this->orientation, 'label']);
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
                    $production->push($mealOrder);
                }
            }

            foreach ($lineItemsOrders as $lineItemOrder) {
                for ($i = 1; $i <= $lineItemOrder->quantity; $i++) {
                    $production->push($lineItemOrder);
                }
            }
        });

        $output = $production->map(function ($item) {
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
        });

        return $output;
    }

    public function export($type)
    {
        if (!in_array($type, ['pdf', 'b64'])) {
            return null;
        }

        Log::info('Starting label print');

        $mealOrders = $this->exportData();
        Log::info('Found ' . count($mealOrders) . ' orders');

        if (!count($mealOrders)) {
            throw new \Exception('No meal orders');
        }

        $filename = 'public/' . md5(time()) . '.pdf';

        $width = $this->params->get('width', 4);
        $height = $this->params->get('height', 6);

        $pdfConfig = [
            'encoding' => 'utf-8',
            'orientation' => $this->orientation,
            'page-width' => $width . 'in',
            'page-height' => $height . 'in',
            'no-outline',
            //'disable-smart-shrinking',
            'no-pdf-compression',
            'javascript-delay' => 1000,
            'debug-javascript',
            'margin-top' => 0,
            'margin-bottom' => 0,
            'margin-left' => 0,
            'margin-right' => 0
        ];

        if (config('pdf.xserver')) {
            $pdfConfig = array_merge($pdfConfig, [
                'use-xserver',
                'commandOptions' => array(
                    'enableXvfb' => true
                )
            ]);
        }

        Log::info($pdfConfig);

        $pdf = new Pdf($pdfConfig);
        $pdf->ignoreWarnings = true;

        $vars = [
            'mealOrder' => null,
            'params' => $this->params,
            'delivery_dates' => $this->getDeliveryDates(),
            'body_classes' => implode(' ', [$this->orientation])
        ];

        Log::info($vars);

        foreach ($mealOrders as $i => $order) {
            if ($i > 0) {
                continue;
            }
            $vars['mealOrder'] = $order;
            $html = view($this->exportPdfView(), $vars)->render();
            Log::info('Page HTML: ' . $html);
            $pdf->addPage($html);
        }

        $output = $pdf->toString();
        $a = $pdf->getCommand()->getOutput(false);

        Log::info('Output: ' . $output);

        if ($pdf->getError()) {
            Log::error('Error: ' . $pdf->getError());
        }

        Log::info('Saved to ' . $filename);

        if ($type === 'pdf') {
            // || $type === 'b64') {
            Storage::disk('local')->put($filename, $output);
            return Storage::url($filename);
        } elseif ($type === 'b64') {
            return base64_encode($output);
        }
    }

    public function exportPdfView()
    {
        return 'reports.label_pdf';
    }
}
