<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use GuzzleHttp\Client;
use App\Store;
use App\Order;

class DeliveryRoutes
{
    use Exportable;

    protected $store;

    public function __construct(Store $store, $params = [])
    {
        $this->store = $store;
        $this->params = $params;
        $this->orientation = 'portrait';
    }

    public function exportData($type = null)
    {
        $dates = $this->getDeliveryDates();
        $orders = $this->store->getOrders(null, $dates, true, true, true);
        $orders = $orders->where('voided', 0);

        // Get all customer addresses from orders

        $id = auth('api')->user()->id;
        $store = Store::where('user_id', $id)->first();
        $storeDetails = $store->details;
        $storeAddress =
            $storeDetails->address .
            ', ' .
            $storeDetails->city .
            ', ' .
            $storeDetails->state .
            ' ' .
            $storeDetails->zip;

        $customerAddresses = [];
        // $customers = [];

        foreach ($orders as $order) {
            $customerDetails = $order->user->details;
            $customerAddresses[] = implode(', ', [
                $customerDetails->address,
                $customerDetails->city,
                $customerDetails->state,
                $customerDetails->zip
            ]);
            // $customers[] = [
            //     'order' => $order,
            //     'name' => $customerDetails->full_name,
            //     'address' => implode(', ', [
            //         $customerDetails->address,
            //         $customerDetails->city,
            //         $customerDetails->state,
            //         $customerDetails->zip
            //     ]),
            //     'phone' => $customerDetails->phone,
            //     'instructions' => $customerDetails->delivery
            // ];
        }

        // return $deliveryAddresses;
    }

    public function exportPdfView()
    {
        return 'reports.delivery_routes_pdf';
    }
}
