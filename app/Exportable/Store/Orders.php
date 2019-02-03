<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;

class Orders
{
    use Exportable;

    protected $store, $params;

    public function __construct(Store $store, $params = [])
    {
        $this->store = $store;
        $this->params = $params;
    }

    public function exportData()
    {
        $orders = $this->store->getOrders(null, $this->getDeliveryDates())->map(function ($order) {
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
