<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Order;

class OrderController extends UserController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = $this->user
            ->orders()
            ->with(['pickup_location', 'purchased_gift_cards'])
            ->where(['paid' => 1])
            ->get();

        $orders->makeHidden([
            'user',
            'items',
            'visible_items',
            'meal_ids',
            'line_items_order',
            'meal_package_items',
            'chargedAmount',
            'currency',
            'order_day',
            'originalAmount',
            'payment_gateway',
            'paid',
            'pickup_location',
            'pickup_location_id',
            'purchasedGiftCardReduction',
            'purchased_gift_card_code',
            'purchased_gift_card_id',
            'stripe_id',
            'transferTime',
            'user_id'
        ]);

        return $orders;
    }

    public function show($id)
    {
        $order = $this->user
            ->orders()
            ->with([
                'user.userDetail',
                'meals',
                'pickup_location',
                'lineItemsOrder',
                'purchased_gift_cards'
            ])
            ->where('id', $id)
            ->first();

        $order->makeHidden([
            'meals',
            'meal_ids',
            'payment_gateway',
            'paid',
            'pickup_location',
            'stripe_id',
            'user_id',
            'visible_items'
        ]);

        if ($this->store && !$this->store->modules->multipleDeliveryDays) {
            $order->makeHidden(['delivery_dates_array', 'isMultipleDelivery']);
        }

        return $order;
    }
}
