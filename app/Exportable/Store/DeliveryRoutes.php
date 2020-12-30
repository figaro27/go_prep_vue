<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use GuzzleHttp\Client;
use App\Store;
use App\Order;
use App\UserDetail;
use App\ReportRecord;

class DeliveryRoutes
{
    use Exportable;

    protected $store;

    protected $headers = [
        'Content-Type' => 'application/json',
        'Authorization' =>
            'Bearer SjA0RntZK8VPMARUgFnE7hX6iZrBO9340Mh35aY7yxWVDUFaVUwP0QOIPLoq'
    ];

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

        $url = "https://app.elasticroute.com/api/v1/plan/asdf?c=sync&w=false";
        $names = [];

        foreach ($orders as $order) {
            $customerDetails = $order->user->details;
            $name =
                $customerDetails->firstname . ' ' . $customerDetails->lastname;

            if (!in_array($name, $names)) {
                $address = implode(', ', [
                    $customerDetails->address,
                    $customerDetails->city,
                    $customerDetails->state,
                    $customerDetails->zip
                ]);

                $stops[] = [
                    "name" => $name,
                    "address" => $address
                ];

                $names[] = $name;
            }
        }

        $depots = [
            [
                "name" => $storeDetails->name,
                "address" => $storeAddress
            ]
        ];

        $vehicles = [
            [
                "name" => "Vehicle 1"
            ]
        ];

        $generalSettings = [
            "country" => $storeDetails->country,
            "timezone" => $store->settings->timezone
        ];

        $client = new \GuzzleHttp\Client();

        try {
            if (isset($stops)) {
                $res = $client->request('POST', $url, [
                    'headers' => $this->headers,
                    'json' => [
                        'stops' => $stops,
                        'depots' => $depots,
                        'vehicles' => $vehicles,
                        'generalSettings' => $generalSettings
                    ]
                ]);

                $status = $res->getStatusCode();
                $body = $res->getBody();

                $data = json_decode($body->getContents());

                $routes[] = [
                    "startingAddress" =>
                        $data->data->details->depots[0]->address,
                    "stops" => $data->data->stats->total_plan_stops,
                    "miles" => ceil(
                        $data->data->stats->total_plan_distance * 0.621371
                    )
                ];

                foreach ($data->data->details->stops as $stop) {
                    // Get the delivery instructions
                    $address = explode(',', $stop->address);

                    $userDetail = UserDetail::where([
                        'address' => $address[0],
                        'city' => ltrim($address[1]),
                        'state' => ltrim($address[2]),
                        'zip' => ltrim($address[3])
                    ])->first();

                    $routes[] = [
                        "name" => $stop->name,
                        "address" => $stop->address,
                        "phone" => $userDetail ? $userDetail->phone : null,
                        "delivery" => $userDetail ? $userDetail->delivery : null
                    ];
                }

                $reportRecord = ReportRecord::where(
                    'store_id',
                    $this->store->id
                )->first();
                $reportRecord->delivery_routes += 1;
                $reportRecord->update();

                return $routes;
            } else {
                dd();
            }
        } catch (\Exception $e) {
            dd();
        }
    }

    public function exportPdfView()
    {
        return 'reports.delivery_routes_pdf';
    }
}
