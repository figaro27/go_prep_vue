<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Store;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;

class DeliveryDayController extends Controller
{
    public function removePastDeliveryDays(Request $request)
    {
        $bagItems = $request->bag;
        if (count($bagItems) > 0) {
            $storeId = $bagItems[0]['delivery_day']['store_id'];

            $store = Store::where('id', $storeId)->first();
            if ($store) {
                $deliveryDays = [];
                foreach ($bagItems as $bagItem) {
                    if (!in_array($bagItem['delivery_day'], $deliveryDays)) {
                        $deliveryDay = $store
                            ->deliveryDays()
                            ->where([
                                'day' => $bagItem['delivery_day']['day'],
                                'type' => $bagItem['delivery_day']['type']
                            ])
                            ->first();
                        if (
                            $deliveryDay->isPastCutoff(
                                $bagItem['delivery_day']['day_friendly'],
                                $bagItem['delivery_day']['type']
                            )
                        ) {
                            $deliveryDay['past_cutoff'] = true;
                        } else {
                            $deliveryDay['past_cutoff'] = false;
                        }
                        $deliveryDay['formattedDayFriendly'] =
                            $bagItem['delivery_day']['day_friendly'];
                        $deliveryDays[] = $deliveryDay;
                    }
                }
                foreach ($deliveryDays as $deliveryDay) {
                    if ($deliveryDay['past_cutoff']) {
                        Artisan::call('cache:clear');
                        $dayFriendly = $deliveryDay['formattedDayFriendly'];
                        return response()->json(
                            [
                                'message' =>
                                    'Orders for ' .
                                    Carbon::parse($dayFriendly)->format(
                                        'D, m/d/y'
                                    ) .
                                    ' have unfortunately passed the cutoff. Day has been removed from your bag. Refreshing the page in 5 seconds.',
                                'error' => 'past_cutoff_delivery_day',
                                'deliveryDay' => $dayFriendly
                            ],
                            400
                        );
                    }
                }
            }
        }
    }
}
