<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;

class Orders
{
    use Exportable;

    protected $store;

    public function __construct(Store $store, $params = [])
    {
        $this->store = $store;
        $this->params = $params;
    }

    public function exportData($type = null)
    {
        $params = $this->params;

        $orders = $this->store
            ->getOrders(null, $this->getDeliveryDates())
            ->filter(function ($order) use ($params) {
                if (
                    $params->has('has_notes') &&
                    $order->has_notes != $params->get('has_notes')
                ) {
                    return false;
                }
                if (
                    $params->has('fulfilled') &&
                    $order->fulfilled != $params->get('fulfilled')
                ) {
                    return false;
                }

                return true;
            })
            ->map(function ($order) {
                return [
                    $order->dailyOrderNumber,
                    $order->order_number,
                    $order->user->details->firstname,
                    $order->user->details->lastname,
                    $order->user->details->address,
                    $order->user->details->zip,
                    $order->user->details->phone,
                    $order->user->email,
                    '$' . number_format($order->amount, 2),
                    '$' . number_format($order->balance, 2),
                    $order->created_at->format('D, m/d/Y'),
                    $order->delivery_date->format('D, m/d/Y'),
                    $order->transferTime
                ];
            });

        if ($type !== 'pdf') {
            $orders->prepend([
                'Daily Order #',
                'Order ID',
                'First Name',
                'Last Name',
                'Address',
                'Zip',
                'Phone',
                'Email',
                'Total',
                'Balance',
                'Order Placed',
                'Delivery Day',
                'Time'
            ]);
        }

        return $orders->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.orders_pdf';
    }
}
