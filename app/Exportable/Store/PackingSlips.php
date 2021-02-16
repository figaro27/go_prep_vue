<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\StoreModule;
use Illuminate\Support\Facades\Storage;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\ReportRecord;

class PackingSlips
{
    use Exportable;

    protected $store;
    protected $orders = [];

    public function __construct(Store $store, $params)
    {
        $this->store = $store;
        $this->params = $params;
        $this->orientation = 'portrait';
        $this->page = $params->get('page', 1);
        $this->perPage = 15;
    }

    public function exportData($type = null)
    {
        $this->params->put('store', $this->store->details->name);
        $this->params->put('report', 'Packing Slips');
        $this->params->put('date', Carbon::now()->format('m-d-Y'));

        $params = $this->params;
        $params['dailyOrderNumbers'] = $this->store->modules->dailyOrderNumbers;

        if (isset($params['order_id']) && (int) $params['order_id'] != 0) {
            $orders = $this->store
                ->orders()
                ->where([
                    'paid' => 1,
                    // 'voided' => 0,
                    'id' => (int) $params['order_id']
                ])
                ->get();
        } else {
            $orders = $this->store->orders()->where([
                'paid' => 1
                // 'voided' => 0
                // 'fulfilled' => 0
            ]);

            $dateRange = $this->getDeliveryDates();

            $orders = $orders->where(function ($query) use ($dateRange) {
                $query
                    ->where(function ($query1) use ($dateRange) {
                        $query1->where('isMultipleDelivery', 0);

                        if ($dateRange === []) {
                            $query1->where(
                                'delivery_date',
                                $this->store->getNextDeliveryDate()
                            );
                        }

                        if (isset($dateRange['from'])) {
                            $from = Carbon::parse($dateRange['from']);
                            $query1->where(
                                'delivery_date',
                                '>=',
                                $from->format('Y-m-d')
                            );
                        }

                        if (isset($dateRange['to'])) {
                            $to = Carbon::parse($dateRange['to']);
                            $query1->where(
                                'delivery_date',
                                '<=',
                                $to->format('Y-m-d')
                            );
                        }
                    })
                    ->orWhere(function ($query2) use ($dateRange) {
                        $query2
                            ->where('isMultipleDelivery', 1)
                            ->whereHas('meal_orders', function (
                                $subquery1
                            ) use ($dateRange) {
                                $subquery1->whereNotNull(
                                    'meal_orders.delivery_date'
                                );

                                if ($dateRange === []) {
                                    $subquery1->where(
                                        'meal_orders.delivery_date',
                                        $this->store->getNextDeliveryDate()
                                    );
                                }

                                if (isset($dateRange['from'])) {
                                    $from = Carbon::parse($dateRange['from']);
                                    $subquery1->where(
                                        'meal_orders.delivery_date',
                                        '>=',
                                        $from->format('Y-m-d')
                                    );
                                }

                                if (isset($dateRange['to'])) {
                                    $to = Carbon::parse($dateRange['to']);
                                    $subquery1->where(
                                        'meal_orders.delivery_date',
                                        '<=',
                                        $to->format('Y-m-d')
                                    );
                                }
                            });
                    });
            });

            // Removing orders from reports that just contain gift cards
            $orders = $orders->where(function ($order) {
                $order
                    ->whereHas('meal_orders')
                    ->orWhereHas('meal_package_orders')
                    ->orWhereHas('lineItemsOrders');
            });

            // Disabled Workflow
            /*
            if ($dateRange === []) {
                $orders = $orders->where(
                    'delivery_date',
                    $this->store->getNextDeliveryDate()
                );
            }
            if (isset($dateRange['from'])) {
                $from = Carbon::parse($dateRange['from']);
                $orders = $orders->where(
                    'delivery_date',
                    '>=',
                    $from->format('Y-m-d')
                );
            }
            if (isset($dateRange['to'])) {
                $to = Carbon::parse($dateRange['to']);
                $orders = $orders->where(
                    'delivery_date',
                    '<=',
                    $to->format('Y-m-d')
                );
            }*/
            if (isset($params['pickupLocationId'])) {
                $orders = $orders->where(
                    'pickup_location_id',
                    $params['pickupLocationId']
                );
            }

            $total = $orders->count();
            $orders = $orders
                ->get()
                ->slice(($this->page - 1) * $this->perPage)
                ->take($this->perPage);
            $numDone = $this->page * $this->perPage;

            if ($numDone < $total) {
                $this->page++;
            } else {
                $this->page = null;
            }
        }

        $reportRecord = ReportRecord::where(
            'store_id',
            $this->store->id
        )->first();
        $reportRecord->packing_slips += 1;
        $reportRecord->update();

        return $orders;
    }

    public function exportPdfView()
    {
        return 'reports.order_packing_slip_pdf';
    }

    public function export($type)
    {
        if (!in_array($type, ['pdf'])) {
            return null;
        }

        Log::info('Starting packing slip print');

        $orders = $this->exportData();

        $vars = [
            'order' => null,
            'params' => $this->params,
            'delivery_dates' => $this->getDeliveryDates(),
            'body_classes' => implode(' ', [$this->orientation]),
            'logo' => ''
        ];

        Log::info('Found ' . count($orders) . ' orders');

        if (!count($orders)) {
            throw new \Exception('No orders');
        }

        $filename = 'public/' . md5(time()) . '.pdf';

        $pdfConfig = [
            'encoding' => 'utf-8',
            'orientation' => $this->orientation,
            'page-size' => 'Letter',
            'no-outline',
            // 'margin-top' => 2,
            // 'margin-bottom' => 2,
            // 'margin-left' => 0,
            // 'margin-right' => 0,
            //'binary' => '/usr/local/bin/wkhtmltopdf',
            'disable-smart-shrinking',
            'no-pdf-compression'
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

        try {
            $logo = \App\Utils\Images::encodeB64(
                $this->store->details->logo['url']
            );
        } catch (\Exception $e) {
            $logo = $this->store->details->logo['url'];
        }

        Log::info('Logo URL: ' . $logo);

        $squareLogo = true;

        if ($logo) {
            if ($this->store->details->host) {
                $logo = 'https://goprep.com' . $logo;
            }

            $logoSize = getImageSize($logo);

            if ($logoSize[0] !== $logoSize[1]) {
                $squareLogo = false;
            }
        }

        $vars = [
            'order' => null,
            'params' => $this->params,
            'delivery_dates' => $this->getDeliveryDates(),
            'body_classes' => implode(' ', [$this->orientation]),
            'logo' => $logo,
            'squareLogo' => $squareLogo
        ];

        Log::info($vars);

        foreach ($orders as $i => $order) {
            $vars['order'] = $order;
            $html = view($this->exportPdfView(), $vars)->render();
            Log::info('Page HTML: ' . $html);
            $pdf->addPage($html);
        }

        if (isset($this->params['order_id'])) {
            $filename =
                'public/' .
                $this->params['store'] .
                ' - ' .
                $orders->toArray()[0]['customer_name'] .
                ' - ' .
                '#' .
                $orders->toArray()[0]['order_number'] .
                ' - ' .
                $this->params['date'] .
                '.pdf';
        } else {
            $filename =
                'public/' .
                $this->params['store'] .
                ' - ' .
                $this->params['report'] .
                ' - ' .
                $this->params['date'] .
                '.pdf';
        }

        $output = $pdf->toString();

        Log::info('Output: ' . $output);

        if ($pdf->getError()) {
            Log::error('Error: ' . $pdf->getError());
        }

        Storage::disk('local')->put($filename, $output);

        Log::info('Saved to ' . $filename);

        return Storage::url($filename);
    }
}
