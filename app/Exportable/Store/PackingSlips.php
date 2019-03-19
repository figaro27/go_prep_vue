<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use Illuminate\Support\Facades\Storage;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Carbon;

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
        $orders = $this->store->orders()->where([
          ['paid', 1],
        ]);

        $dateRange = $this->getDeliveryDates();
        if ($dateRange === []) {
            $orders = $orders->where('delivery_date', $this->store->getNextDeliveryDate());
        }
        if (isset($dateRange['from'])) {
            $from = Carbon::parse($dateRange['from']);
            $orders = $orders->where('delivery_date', '>=', $from->format('Y-m-d'));
        }
        if (isset($dateRange['to'])) {
            $to = Carbon::parse($dateRange['to']);
            $orders = $orders->where('delivery_date', '<=', $to->format('Y-m-d'));
        }

        $orders = $orders->get();

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

        $orders = $this->exportData();

        if (!count($orders)) {
            throw new \Exception('No orders');
        }

        $filename = 'public/' . md5(time()) . '.pdf';

        $pdfConfig = ['encoding' => 'utf-8'];

        if (config('pdf.xserver')) {
            $pdfConfig = array_merge($pdfConfig, [
                'use-xserver',
                'commandOptions' => array(
                    'procEnv' => array('DISPLAY' => ':0'),
                ),
            ]);
        }

        $pdf = new Pdf($pdfConfig);

        try {
          $logo = \App\Utils\Images::encodeB64(
            url($this->store->details->logo)
          );
        }
        catch(\Exception $e) {
          $logo = $this->store->getUrl($this->store->details->logo);
        }

        $vars = [
          'order' => null,
          'params' => $this->params,
          'delivery_dates' => $this->getDeliveryDates(),
          'body_classes' => implode(' ', [$this->orientation]),
          'logo' => $logo,
        ];

        foreach ($orders as $i => $order) {
            $vars['order'] = $order;
            $html = view($this->exportPdfView(), $vars)->render();
            $pdf->addPage($html);
        }

        $output = $pdf->toString();

        Storage::disk('local')->put($filename, $output);
        return Storage::url($filename);

    }
}
