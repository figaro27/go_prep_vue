<?php

namespace App\Http\Controllers;

use Auth;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\StoreDetail;
use App\Order;
use App\Store;

class DeliveryRouteController extends Controller
{
    public function getRoutes()
    {
        $googleApiKey = 'AIzaSyArp-lohyOEQXF6a69wyFXruthJd9jNY4U';
        $hereApp_id = "V2tJJFOIa2LjoSw4xNuX";
        $hereApp_code = "JRGmnV2itkv7cCLRWc55CA";
        // $hereApp_id="D2vwjQLe6hZEsNkdzPf0";
        // $hereApp_code="_0IsSILsI4W-7piSDVl81A";

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
        $customerAddressesNatural = [];

        // Transfer to Exportable and get delivery dates for accurate orders

        $orders = Order::where('store_id', $store->id)->get();

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

            array_push(
                $customerAddressesNatural,
                $customerAddress->address .
                    ' ' .
                    $customerAddress->city .
                    ' ' .
                    $customerAddress->state .
                    ' ' .
                    $customerAddress->zip
            );
        }

        // Convert store address to geocode

        $googleClient = new Client();

        $storeCoordinates = '';
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
            array_push($order, $waypoint->id);
        }

        // Fix this below, maybe get delivery instructions, get accurate delivery date for orders, and should be done
        $output = array_merge($customerAddressesNatural, $order);
        return $output;
    }
}
