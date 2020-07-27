<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use Detrack\ElasticRoute\Plan;
use GuzzleHttp\Client;

class DeliveryRouteController extends StoreController
{
    protected $headers = [
        'Content-Type' => 'application/json',
        'Authorization' =>
            'Bearer SjA0RntZK8VPMARUgFnE7hX6iZrBO9340Mh35aY7yxWVDUFaVUwP0QOIPLoq'
    ];

    public function getRoutes(Request $request)
    {
        // Plan::$defaultApiKey =
        //     "SjA0RntZK8VPMARUgFnE7hX6iZrBO9340Mh35aY7yxWVDUFaVUwP0QOIPLoq";

        // $plan = new Plan();
        // $plan->id = "my_first_plan";

        // $plan->stops = [
        //     [
        //         "name" => "Mikes Old House",
        //         "address" => "1622 Bay Ridge Ave, Brooklyn, NY 11204"
        //     ],
        //     [
        //         "name" => "Mikes New House",
        //         "address" => "244 92nd St, Brooklyn, NY 11209"
        //     ]
        // ];

        // $plan->vehicles = [
        //     [
        //         "name" => "Van 1"
        //     ]
        // ];

        // $plan->depots = [
        //     [
        //         "name" => "Main Warehouse",
        //         "address" => "8701 4th Ave, Brooklyn, NY 11209"
        //     ]
        // ];

        // $plan->generalSettings["country"] = "US";
        // $plan->generalSettings["timezone"] = "America/New_York";

        // $solution = $plan->solve();

        // return $solution;

        // $client = new \GuzzleHttp\Client();

        // #Sends the stops data
        // $data = '{
        //   "data": [
        //     {
        //       "name": "Stop1",
        //       "address": “1622 Bay Ridge Ave, Brooklyn, NY 11204"
        //     },
        //     {
        //       "name": "Stop2",
        //       "address": "244 92nd St, Brooklyn, NY 11209"
        //     },
        //     {
        //       "name": "Stop3",
        //       "address": "8701 4th Ave, Brooklyn, NY 11209"
        //     }
        //   ]
        // }';

        // $date = "2020-07-30";

        // $url = "https://app.elasticroute.com/api/v1/account/stops/{$date}/bulk";

        // $response = $client -> request('POST', $url, [
        //     'headers' => [
        //       'Content-Type' => 'application/json',
        //       'Authorization' => 'Bearer SjA0RntZK8VPMARUgFnE7hX6iZrBO9340Mh35aY7yxWVDUFaVUwP0QOIPLoq'
        //     ],
        //     'form_params' => [
        //         'body' => []
        //     ]
        //   ]
        // );

        // return $response;

        // #Triggers the planning
        // $url_startPlan = "https://app.elasticroute.com/api/v1/account/stops/{$date}/plan";

        // $response_start = $client -> request('POST', $url_startPlan, [
        //     'headers' => [
        //       'Content-Type' => 'application/json',
        //       'Authorization' => 'Bearer SjA0RntZK8VPMARUgFnE7hX6iZrBO9340Mh35aY7yxWVDUFaVUwP0QOIPLoq'
        //     ],
        //     ,
        //     'body' => $data,
        //   ]
        // );

        // #Retrieve the planned data
        // $url_planned_data = "https://app.elasticroute.com/api/v1/account/stops/{$date}/plan/status";

        // $response_planned_data = $client -> request('GET', $url_planned_data, [
        //     'headers' => [
        //       'Content-Type' => 'application/json',
        //       'Authorization' => 'Bearer SjA0RntZK8VPMARUgFnE7hX6iZrBO9340Mh35aY7yxWVDUFaVUwP0QOIPLoq'
        //     ],
        //     ,
        //     'body' => $data,
        //   ]
        // );

        // return $response_planned_data;

        $url = "https://app.elasticroute.com/api/v1/plan/asdf?c=sync&w=false";

        $stops = [
            [
                "name" => "Mikes Old House",
                "address" => "1622 Bay Ridge Ave, Brooklyn, NY 11204"
            ],
            [
                "name" => "Mikes New House",
                "address" => "244 92nd St, Brooklyn, NY 11209"
            ]
        ];

        $vehicles = [
            [
                "name" => "Van 1"
            ]
        ];

        $depots = [
            [
                "name" => "Main Warehouse",
                "address" => "8701 4th Ave, Brooklyn, NY 11209"
            ]
        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $url, [
            'headers' => $this->headers,
            'form_params' => [
                'stops' => $stops,
                'depots' => $depots,
                'vehicles' => $vehicles
            ]
        ]);

        $status = $res->getStatusCode();
        $body = $res->getBody();

        return $body;
    }
}
