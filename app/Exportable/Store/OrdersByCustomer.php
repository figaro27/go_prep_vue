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
    }

    public function exportData($type = null)
    {
        $dateRange = $this->getDeliveryDates();
        $params = $this->params;

        if ($params->has('fulfilled')){
            $fulfilled = $params->get('fulfilled');
        }
        else
            $fulfilled = 0;

        $orders = $this->store->orders()->where('fulfilled', $fulfilled)->get()->groupBy('user_id');

        if (isset($dateRange['from'])) {
            $from = Carbon::parse($dateRange['from']);
            $orders = $orders->where('delivery_date', '>=', $from->format('Y-m-d'));
        }
        if (isset($dateRange['to'])) {
            $to = Carbon::parse($dateRange['to']);
            $orders = $orders->where('delivery_date', '<=', $to->format('Y-m-d'));
        }



        $customerOrders = $orders
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
                            'meal_quantities' => array_merge(
                                [['Meal', 'Quantity']], // Heading
                                $order->meals->map(function ($meal) {
                                    return [
                                        'title' => $meal->title,
                                        'quantity' => $meal->pivot->quantity ?? 1,
                                    ];
                                })->toArray()
                            ),
                        ];
                    }),
                ];
            });

        return $customerOrders->values();
    }

    public function exportPdfView()
    {
        return 'reports.orders_by_customer_pdf';
    }
}
