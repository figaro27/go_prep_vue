<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;

class PastOrders
{
    use Exportable;

    protected $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function exportData()
    {
        $orders = $this->store->getFulfilledOrders()->map(function ($order) {
          return [
            $order->delivery,
            $order->order_number,
            $order->user->name,
            $order->user->details->address,
            $order->user->details->zip,
            $order->user->details->phone,
            $order->amount,
            $order->created_at,
            $order->delivery_date,
          ];
        });

        return $orders->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.orders_pdf';
    }
}
