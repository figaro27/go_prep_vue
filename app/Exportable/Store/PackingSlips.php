<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\StoreModule;
use Illuminate\Support\Facades\Storage;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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
    }

    public function exportData($type = null)
    {
        $params = $this->params;
        $params['dailyOrderNumbers'] = $this->store->modules->dailyOrderNumbers;

        if (isset($params['order_id']) && (int) $params['order_id'] != 0) {
            $orders = $this->store
                ->orders()
                ->where([
                    'paid' => 1,
                    'voided' => 0,
                    'id' => (int) $params['order_id']
                ])
                ->get();
        } else {
            $orders = $this->store->orders()->where([
                'paid' => 1,
                'voided' => 0
                // 'fulfilled' => 0
            ]);

            $dateRange = $this->getDeliveryDates();
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
            }

            $orders = $orders->get();
        }

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
            //'margin-top' => 0,
            //'margin-bottom' => 0,
            //'margin-left' => 0,
            //'margin-right' => 0,
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
                $this->store->details->logo['url_thumb']
            );
        } catch (\Exception $e) {
            $logo = $this->store->details->logo['url_thumb'];
        }

        Log::info('Logo URL: ' . $logo);

        $vars = [
            'order' => null,
            'params' => $this->params,
            'delivery_dates' => $this->getDeliveryDates(),
            'body_classes' => implode(' ', [$this->orientation]),
            'logo' => $logo
        ];

        Log::info($vars);

        foreach ($orders as $i => $order) {
            $vars['order'] = $order;
            $html = view($this->exportPdfView(), $vars)->render();
            Log::info('Page HTML: ' . $html);
            $pdf->addPage($html);
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
