<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use GuzzleHttp\Client;
use App\Store;
use App\Order;
use App\UserDetail;
use App\ReportRecord;
use Illuminate\Support\Carbon;

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
        $this->params->put('store', $this->store->details->name);
        $this->params->put('report', 'Orders');
        $this->params->put('date', Carbon::now()->format('m-d-Y'));
        $this->orientation = 'portrait';
    }

    public function exportData($type = null)
    {
        $this->params->put('store', $this->store->details->name);
        $this->params->put('report', 'Delivery');
        $this->params->put('date', Carbon::now()->format('m-d-Y'));

        $dates = $this->getDeliveryDates();
        $orders = $this->store->getOrders(null, $dates, true, true, true);
        $orders = $orders->where('voided', 0);
        $orderByRoutes = $this->params['orderByRoutes'];

        $id = auth('api')->user()->id;
        $store = Store::where('user_id', $id)->first();
        $storeDetails = $store->details;

        $start = json_decode($this->params->get('startingAddress'), true);
        $startingAddress =
            $start['address'] .
            ', ' .
            $start['city'] .
            ', ' .
            $storeDetails->state .
            ', ' .
            $start['zip'];
        $end = json_decode($this->params->get('endingAddress'), true);
        $endingAddress =
            $end['address'] .
            ', ' .
            $end['city'] .
            ', ' .
            $storeDetails->state .
            ', ' .
            $end['zip'];

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

                $recipients[] = [
                    "name" =>
                        $customerDetails->firstname .
                        ' ' .
                        $customerDetails->lastname,
                    "phone" => $customerDetails->phone,
                    "address" => $customerDetails->address,
                    "city" => $customerDetails->city,
                    "state" => $customerDetails->state,
                    "zip" => $customerDetails->zip,
                    "delivery" => $customerDetails->delivery
                ];
            }
        }

        $depots = [
            [
                "name" => $storeDetails->name,
                "address" => $startingAddress
            ]
        ];

        $endingDepot = [
            [
                "name" => $storeDetails->name . ' - Ending Depot',
                "address" => $endingAddress
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
                if ($orderByRoutes === "true") {
                    $res = $client->request('POST', $url, [
                        'headers' => $this->headers,
                        'json' => [
                            'stops' => $stops,
                            'depots' => $depots,
                            'end_depot' => $endingDepot,
                            'vehicles' => $vehicles,
                            'generalSettings' => $generalSettings
                        ]
                    ]);

                    $status = $res->getStatusCode();
                    $body = $res->getBody();

                    $data = json_decode($body->getContents());

                    $routes[] = [
                        "startingAddress" => $startingAddress,
                        "endingAddress" => $endingAddress,
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
                            "delivery" => $userDetail
                                ? $userDetail->delivery
                                : null
                        ];
                    }
                }

                $reportRecord = ReportRecord::where(
                    'store_id',
                    $this->store->id
                )->first();
                $reportRecord->delivery_routes += 1;
                $reportRecord->update();

                if ($orderByRoutes === "true") {
                    if ($type !== 'pdf') {
                        array_shift($routes);
                        return $this->formatRecipients($routes, $type);
                    } else {
                        return $routes;
                    }
                } else {
                    return $this->formatRecipients($recipients, $type);
                }
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

    public function formatRecipients($recipients, $type)
    {
        $recipients = collect($recipients);
        // Customer report format for Eat Right Meal Prep
        if (
            $type !== 'pdf' &&
            $this->params['orderByRoutes'] === "false" &&
            ($this->store->id === 196 || $this->store->id === 3)
        ) {
            $recipients = $recipients->map(function ($recipient) {
                return [
                    $recipient['phone'],
                    $recipient['name'],
                    $recipient['address'],
                    $recipient['city'] .
                        ' ' .
                        $recipient['state'] .
                        ' ' .
                        $recipient['zip'],
                    $recipient['delivery']
                ];
            });

            $recipients->prepend([
                'Phone Number',
                'Customer Name',
                'Delivery Address',
                'Delivery City, State, Zip',
                'Customer Message'
            ]);
        } else {
            if ($this->params['orderByRoutes'] === "true") {
                $recipients->prepend([
                    'Customer Name',
                    'Address',
                    'Phone',
                    'Delivery Instructions'
                ]);
            } else {
                $recipients->prepend([
                    'Customer Name',
                    'Phone',
                    'Address',
                    'City',
                    'State',
                    'Zip',
                    'Delivery Instructions'
                ]);
            }
        }

        return $recipients;
    }
}
