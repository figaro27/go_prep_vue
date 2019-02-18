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

    public function exportData($type = null)
    {
        $orders = $this->store->getOrders(null, $this->getDeliveryDates())->map(function ($order) {
          return [
            $order->order_number,
            $order->user->name,
            $order->user->details->address,
            $order->user->details->zip,
            $order->user->details->phone,
            '$'.$order->amount,
            $order->created_at->format('D, m/d/Y'),
            $order->delivery_date->format('D, m/d/Y'),
          ];
        });

        if($type !== 'pdf'){
            $orders->prepend(['Order #', 'Name', 'Address', 'Zip', 'Phone', 'Total', 'Order Placed', 'Delivery Day' ]);
        }

        return $orders->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.orders_pdf';
    }
}
