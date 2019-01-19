<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;

class OrdersByCustomer
{
    use Exportable;

    protected $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function exportData()
    {
        $customerOrders = $this->store->getOrdersForNextDelivery('user_id')
            ->map(function ($orders, $userId) {
              return [
                'user' => User::find($userId),
                'orders' => $orders->map(function($order) {
                  return [
                    'id' => $order->id,
                    'meal_quantities' => array_merge(
                      [['Meal', 'Quantity']], // Heading
                      $order->meals->map(function($meal) {
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
