<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use Illuminate\Support\Facades\Storage;
use mikehaertl\wkhtmlto\Pdf;

class PackingSlips
{
    use Exportable;

    protected $store;
    protected $orders = [];

    public function __construct(Store $store)
    {
        $this->store = $store;
        $this->orders = $store->getOrdersForNextDelivery();
    }

    public function exportData()
    {
        return $this->orders;
        $orders = $this->orders->map(function($order) {
          return $order;
        });

        return $orders->toArray();
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

        if(!count($orders)) {
          throw new \Exception('No orders');
        }

        $filename = 'public/' . md5(time()) . '.pdf';

        $pdf = new Pdf([
            'encoding' => 'UTF-8',
        ]);
        
        foreach($orders as $i=> $order) {
          $html = view($this->exportPdfView(), ['order' => $order])->render();
          $pdf->addPage($html);
        }

        $output = $pdf->toString();

        Storage::disk('local')->put($filename, $output);
        return Storage::url($filename);

    }
}
