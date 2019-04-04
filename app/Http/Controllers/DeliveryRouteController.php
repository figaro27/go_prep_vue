<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\StoreDetail;
use App\Order;

class DeliveryRouteController extends Controller
{
    public function getRoutes()
    {
        $googleApiKey = 'AIzaSyArp-lohyOEQXF6a69wyFXruthJd9jNY4U';

        $client = new Client([
            'base_uri' => 'https://maps.googleapis.com/maps/api/directions/'
        ]);

        // Get the correct store - move controller to Store folder? And to routes/api.
        $store = StoreDetail::first();
        $address =
            $store->address .
            ' ' .
            $store->city .
            ' ' .
            $store->state .
            ' ' .
            $store->zip;
        $storeAddress = str_replace(' ', '+', $address);

        $customerAddresses = [];

        // Change to only orders for the requested delivery date(s);
        // Google only allows 23 Waypoints at a time. Need to do this in multiple requests
        $orders = Order::where('store_id', $store->id)->take(20);

        foreach ($orders as $order) {
            $customerAddress = $order->user->details;
            array_push(
                $customerAddresses,
                str_replace(' ', '+', $customerAddress->address) .
                    '+' .
                    $customerAddress->city .
                    '+' .
                    $customerAddress->state .
                    '+' .
                    $customerAddress->zip
            );
        }

        $customerAddresses = implode("|", $customerAddresses);

        $res = $client->get(
            'json?origin=' .
                $storeAddress .
                '&destination=' .
                $storeAddress .
                '&waypoints=optimize:true|' .
                $customerAddresses .
                '
        |&key=' .
                $googleApiKey
        );

        return $res->getBody();

        // Output into a report.
    }
}
