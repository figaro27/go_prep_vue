<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use Detrack\ElasticRoute\Plan;

class DeliveryRouteController extends StoreController
{
    public function getRoutes(Request $request)
    {
        Plan::$defaultApiKey =
            "SjA0RntZK8VPMARUgFnE7hX6iZrBO9340Mh35aY7yxWVDUFaVUwP0QOIPLoq";

        $plan = new Plan();
        $plan->id = "my_first_plan";

        $plan->stops = [
            [
                "name" => "Mikes Old House",
                "address" => "1622 Bay Ridge Ave, Brooklyn, NY 11204"
            ],
            [
                "name" => "Mikes New House",
                "address" => "244 92nd St, Brooklyn, NY 11209"
            ]
        ];

        $plan->vehicles = [
            [
                "name" => "Van 1"
            ]
        ];

        $plan->depots = [
            [
                "name" => "Main Warehouse",
                "address" => "8701 4th Ave, Brooklyn, NY 11209"
            ]
        ];

        $plan->generalSettings["country"] = "US";
        $plan->generalSettings["timezone"] = "America/New_York";

        $solution = $plan->solve();

        return $solution;
    }
}
