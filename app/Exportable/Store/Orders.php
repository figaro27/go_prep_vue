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

        if (
            $this->store->id == 108 ||
            $this->store->id == 109 ||
            $this->store->id == 110
        ) {
            $params->put('livotis', true);
        }

        $params->put(
            'show_daily_order_numbers',
            $this->store->modules->dailyOrderNumbers
        );

        $params->put(
            'show_times',
            $this->store->modules->pickupHours ||
                $this->store->modules->deliveryHours
        );

        $orders = $this->store
            ->getOrders(null, $this->getDeliveryDates())
            ->filter(function ($order) use ($params) {
                if (
                    $params->has('fulfilled') &&
                    $order->fulfilled != $params->get('fulfilled')
                ) {
                    return false;
                }

                return true;
            })
            ->map(function ($order) {
                // if ($this->params->get('livotis')) {
                //     return [
                //         $order->dailyOrderNumber,
                //         $order->user->details->lastname,
                //         $order->user->details->firstname,
                //         $order->user->details->phone,
                //         $order->transferTime,
                //         '$' . number_format($order->amount, 2),
                //         '$' . number_format($order->balance, 2),
                //         $order->pickup ? 'Pickup' : 'Delivery'
                //     ];
                // } else {
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
                    !$order->isMultipleDelivery
                        ? $order->delivery_date->format('D, m/d/Y')
                        : 'Multiple',
                    $order->transferTime
                ];
                // }
            });

        if ($type !== 'pdf') {
            if ($this->params->get('livotis')) {
                $orders->prepend([
                    'Daily Order #',
                    'Last Name',
                    'First Name',
                    'Phone',
                    'Time',
                    'Amount',
                    'Balance',
                    'Type'
                ]);
            } else {
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
        }

        $orders = $orders->toArray();

        // Removing Daily Order Number column if module is not enabled
        if (!$this->params['show_daily_order_numbers']) {
            for ($i = 0; $i < count($orders); $i++) {
                array_shift($orders[$i]);
            }
        }

        // Removing Time column if pickupHours & deliveryHours columns are not enabled
        if (!$this->params['show_times']) {
            for ($i = 0; $i < count($orders); $i++) {
                array_pop($orders[$i]);
            }
        }

        return $orders;
    }

    public function exportPdfView()
    {
        return 'reports.orders_pdf';
    }
}
