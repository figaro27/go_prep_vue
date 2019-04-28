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
    }

    public function exportData($type = null)
    {
        $dates = $this->getDeliveryDates();
        $orders = $this->store->getOrders(null, $dates, true);

        $googleApiKey = 'AIzaSyArp-lohyOEQXF6a69wyFXruthJd9jNY4U';
        $hereApp_id = "V2tJJFOIa2LjoSw4xNuX";
        $hereApp_code = "JRGmnV2itkv7cCLRWc55CA";
        // $hereApp_id = "D2vwjQLe6hZEsNkdzPf0";
        // $hereApp_code = "_0IsSILsI4W-7piSDVl81A";

        // Get all customer addresses from orders

        $id = auth('api')->user()->id;
        $store = Store::where('user_id', $id)->first();
        $storeDetails = $store->details;
        $address =
            $storeDetails->address .
            ' ' .
            $storeDetails->city .
            ' ' .
            $storeDetails->state .
            ' ' .
            $storeDetails->zip;
        $storeAddress = str_replace(' ', '+', $address);

        $customerAddresses = [];
        $customers = [];

        $orders = Order::where('store_id', $store->id)
            ->where('pickup', 0)
            ->get();

        foreach ($orders as $order) {
            $customerDetails = $order->user->details;
            array_push(
                $customerAddresses,
                str_replace(' ', '+', $customerDetails->address) .
                    '+' .
                    $customerDetails->city .
                    '+' .
                    $customerDetails->state .
                    '+' .
                    $customerDetails->zip
            );

            array_push(
                $customers,
                $customerDetails->firstname .
                    ' ' .
                    $customerDetails->lastname .
                    '|' .
                    $customerDetails->address .
                    ', ' .
                    $customerDetails->city .
                    ', ' .
                    $customerDetails->state .
                    ' ' .
                    $customerDetails->zip .
                    '|' .
                    $customerDetails->delivery
            );
        }

        // Convert store address to geocode

        $googleClient = new Client();

        $storeCoordinates = '';
        $res = $googleClient->get(
            'https://maps.googleapis.com/maps/api/geocode/json?address=' .
                $storeAddress .
                '&key=' .
                $googleApiKey
        );
        $response = $res->getBody();
        $body = json_decode($response);
        $latitude = $body->results[0]->geometry->location->lat;
        $longitude = $body->results[0]->geometry->location->lng;
        $storeCoordinates .= $latitude . ',' . $longitude;

        // Convert customer addresses to geocodes

        $coordinates = [];

        foreach ($customerAddresses as $customerAddress) {
            $res = $googleClient->get(
                'https://maps.googleapis.com/maps/api/geocode/json?address=' .
                    $customerAddress .
                    '&key=' .
                    $googleApiKey
            );
            $response = $res->getBody();
            $body = json_decode($response);
            $latitude = $body->results[0]->geometry->location->lat;
            $longitude = $body->results[0]->geometry->location->lng;
            array_push($coordinates, $latitude . ',' . $longitude);
            sleep(0.25);
        }

        // Append all geocoded addresses together

        $hereClient = new Client();

        $i = 1;
        $coordinatesString = '&destination' . $i . '=';
        $len = count($coordinates);

        foreach ($coordinates as $coordinate) {
            $i++;
            $coordinatesString .= $coordinate;
            if ($i != $len + 1) {
                $coordinatesString .= '&destination' . $i . '=';
            }
        }

        // Get the optimal delivery route order

        $res = $hereClient->get(
            'https://wse.api.here.com/2/findsequence.json?start=Start;' .
                $storeCoordinates .
                $coordinatesString .
                '&mode=fastest;car&app_id=' .
                $hereApp_id .
                '&app_code=' .
                $hereApp_code
        );

        $response = $res->getBody();

        $body = json_decode($response);

        $waypoints = $body->results[0]->waypoints;

        $order = [];

        foreach ($waypoints as $waypoint) {
            if ($waypoint->id != "Start") {
                array_push($order, (int) substr($waypoint->id, -1));
            }
        }

        $deliveryAddresses = array_map(function ($item) use ($customers) {
            return $customers[$item];
        }, $order);

        return $deliveryAddresses;
    }

    public function exportPdfView()
    {
        return 'reports.delivery_routes_pdf';
    }
}
