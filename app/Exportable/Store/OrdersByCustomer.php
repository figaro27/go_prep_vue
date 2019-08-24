<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use Illuminate\Support\Carbon;

class OrdersByCustomer
{
    use Exportable;

    protected $store;

    public function __construct(Store $store, $params)
    {
        $this->store = $store;
        $this->params = $params;
        $this->orientation = 'portrait';
    }

    public function exportData($type = null)
    {
        $dateRange = $this->getDeliveryDates();
        $params = $this->params;

        // if ($params->has('fulfilled')) {
        //     $fulfilled = $params->get('fulfilled');
        // } else {
        //     $fulfilled = 0;
        // }

        $orders = $this->store->orders()->where(['paid' => 1]);
        // ->where(['fulfilled' => $fulfilled, 'paid' => 1]);

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

        $customerOrders = $orders
            ->with(['meal_orders', 'lineItemsOrders'])
            ->get()
            ->groupBy('user_id')
            ->map(function ($orders, $userId) {
                return [
                    'user' => User::find($userId),
                    'orders' => $orders->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'address' => $order->user->userDetail->address,
                            'city' => $order->user->userDetail->city,
                            'state' => $order->user->userDetail->state,
                            'zip' => $order->user->userDetail->zip,
                            'delivery' => $order->user->userDetail->delivery,
                            'pickup' => $order->pickup,
                            'pickup_location_id' => $order->pickup_location_id,
                            'pickup_location' => $order->pickup_location,
                            'meal_quantities' => array_merge(
                                [['Meal', 'Quantity']], // Heading
                                $order
                                    ->meal_orders()
                                    ->get()
                                    ->map(function ($mealOrder) {
                                        return [
                                            'title' =>
                                                $this->type !== 'pdf'
                                                    ? $mealOrder->title
                                                    : $mealOrder->html_title,
                                            'quantity' =>
                                                $mealOrder->quantity ?? 1
                                        ];
                                    })
                                    ->toArray()
                            ),
                            'lineItemsOrders' => array_merge(
                                [['Extras', 'Quantity']], // Heading
                                $order
                                    ->lineItemsOrders()
                                    ->get()
                                    ->map(function ($lineItemOrder) {
                                        return [
                                            'title' => $lineItemOrder->title,
                                            'quantity' =>
                                                $lineItemOrder->quantity
                                        ];
                                    })
                                    ->toArray()
                            )
                        ];
                    })
                ];
            });

        return $customerOrders->values();
    }

    public function exportPdfView()
    {
        return 'reports.orders_by_customer_pdf';
    }
}
