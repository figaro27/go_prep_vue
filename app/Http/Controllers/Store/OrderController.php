<?php

namespace App\Http\Controllers\Store;

use App\Order;
use App\Bag;
use App\MealOrder;
use App\MealOrderComponent;
use App\MealOrderAddon;
use App\LineItem;
use App\LineItemOrder;
use App\MealAttachment;
use Illuminate\Http\Request;
use App\Http\Controllers\Store\StoreController;
use Illuminate\Support\Carbon;
use DB;

class OrderController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->has('orders')
            ? $this->store
                ->orders()
                ->with(['user', 'pickup_location'])
                ->where(['paid' => 1])
                ->get()
            : [];
    }

    public function getUpcomingOrders()
    {
        $fromDate = Carbon::today(
            $this->store->settings->timezone
        )->startOfDay();

        return $this->store->has('orders')
            ? $this->store
                ->orders()
                ->with(['user', 'pickup_location'])
                ->where(['paid' => 1])
                ->where('delivery_date', '>=', $fromDate)
                ->get()
            : [];
    }

    public function getOrdersToday()
    {
        $fromDate = Carbon::today(
            $this->store->settings->timezone
        )->startOfDay();

        return $this->store->has('orders')
            ? $this->store
                ->orders()
                ->with(['user', 'pickup_location'])
                ->where(['paid' => 1])
                ->where('created_at', '>=', $fromDate)
                ->get()
            : [];
    }

    public function getFulfilledOrders()
    {
        return $this->store->has('orders')
            ? $this->store
                ->orders()
                ->with(['user', 'pickup_location'])
                ->where(['paid' => 1, 'fulfilled' => 1])
                ->get()
            : [];
    }

    public function getOrdersWithDates(Request $request)
    {
        $paymentsPage = $request->get('payments');

        if ($request->get('end') != null) {
            $endDate = $request->get('end');
        } else {
            $endDate = $request->get('start');
        }

        $date = '';
        if ($paymentsPage) {
            $date = 'created_at';
        } else {
            $date = 'delivery_date';
        }

        return $this->store->has('orders')
            ? $this->store
                ->orders()
                ->with(['user', 'pickup_location'])
                ->where(['paid' => 1])
                ->where($date, '>=', $request->get('start'))
                ->where($date, '<=', $endDate)
                ->get()
            : [];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->store
            ->orders()
            ->with([
                'user',
                'user.userDetail',
                'meals',
                'pickup_location',
                'lineItemsOrder'
            ])
            ->where('id', $id)
            ->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return Order::updateOrder($id, $request->all());
    }

    public function adjustOrder(Request $request)
    {
        $order = Order::where('id', $request->get('orderId'))->first();
        $store = $order->store;
        $bag = new Bag($request->get('bag'), $store);
        $couponId = $request->get('coupon_id');
        $couponReduction = $request->get('couponReduction');
        $couponCode = $request->get('couponCode');
        $deliveryFee = $request->get('deliveryFee');
        $deliveryDate = $request->get('deliveryDate');
        $pickupLocation = $request->get('pickupLocation');
        $transferTime = $request->get('transferTime');
        $bagTotal = $bag->getTotal() + $request->get('lineItemTotal');
        $subtotal = $request->get('subtotal');
        $afterDiscountBeforeFees = $bagTotal;
        $preFeePreDiscount = $bagTotal;
        $processingFee = 0;
        $mealPlanDiscount = 0;
        $salesTax = $request->get('salesTax');
        $deliveryFee = $request->get('deliveryFee');
        $processingFee = $request->get('processingFee');
        $cashOrder = $request->get('cashOrder');
        $grandTotal = $request->get('grandTotal');
        $adjustedDifference = $request->get('grandTotal') - $order->amount;
        $deposit =
            (($order->deposit * $order->amount) / 100 / $grandTotal) * 100;
        $originalDeliveryDate = $order->delivery_date;
        $order->delivery_date = $deliveryDate;
        $order->transferTime = $request->get('transferTime');
        $order->adjusted = 1;
        $order->pickup = $request->get('pickup');
        $order->preFeePreDiscount = $preFeePreDiscount;
        $order->mealPlanDiscount = $mealPlanDiscount;
        $order->afterDiscountBeforeFees = $afterDiscountBeforeFees;
        $order->deliveryFee = $deliveryFee;
        $order->processingFee = $processingFee;
        $order->salesTax = $salesTax;
        $order->amount = $grandTotal;
        $order->deposit = $deposit;
        $order->adjustedDifference = $adjustedDifference;
        $order->coupon_id = $couponId;
        $order->couponReduction = $couponReduction;
        $order->couponCode = $couponCode;
        $order->coupon_id = $couponId;
        $order->couponReduction = $couponReduction;
        $order->couponCode = $couponCode;
        $order->pickup_location_id = $pickupLocation;
        $order->transferTime = $transferTime;

        $max = Order::where('store_id', $store->id)
            ->whereDate('delivery_date', $deliveryDate)
            ->max('dailyOrderNumber');
        $dailyOrderNumber = $max + 1;

        if ($originalDeliveryDate != $deliveryDate) {
            $order->dailyOrderNumber = $dailyOrderNumber;
        }

        $order->save();

        $order->meal_orders()->delete();
        foreach ($bag->getItems() as $item) {
            $mealOrder = new MealOrder();
            $mealOrder->order_id = $order->id;
            $mealOrder->store_id = $store->id;
            $mealOrder->meal_id = $item['meal']['id'];
            $mealOrder->quantity = $item['quantity'];
            if (isset($item['size']) && $item['size']) {
                $mealOrder->meal_size_id = $item['size']['id'];
            }
            $mealOrder->save();

            if (isset($item['components']) && $item['components']) {
                foreach ($item['components'] as $componentId => $choices) {
                    foreach ($choices as $optionId) {
                        MealOrderComponent::create([
                            'meal_order_id' => $mealOrder->id,
                            'meal_component_id' => $componentId,
                            'meal_component_option_id' => $optionId
                        ]);
                    }
                }
            }

            if (isset($item['addons']) && $item['addons']) {
                foreach ($item['addons'] as $addonId) {
                    MealOrderAddon::create([
                        'meal_order_id' => $mealOrder->id,
                        'meal_addon_id' => $addonId
                    ]);
                }
            }
        }

        $lineItemsOrder = $request->get('lineItemsOrder');
        if ($lineItemsOrder != null) {
            foreach ($lineItemsOrder as $lineItemOrder) {
                $title = $lineItemOrder['title'];
                $id = LineItem::where('title', $title)
                    ->pluck('id')
                    ->first();
                $quantity = $lineItemOrder['quantity'];
                $existingLineItem = LineItemOrder::where([
                    'line_item_id' => $id,
                    'order_id' => $order->id
                ])->first();

                if ($existingLineItem) {
                    $existingLineItem->quantity = $quantity;
                    $existingLineItem->save();
                } else {
                    $lineItemOrder = new LineItemOrder();
                    $lineItemOrder->store_id = $store->id;
                    $lineItemOrder->line_item_id = $id;
                    $lineItemOrder->order_id = $order->id;
                    $lineItemOrder->quantity = $quantity;
                    $lineItemOrder->save();
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }

    public function updateViewed()
    {
        Order::where('viewed', 0)->update(['viewed' => 1]);
    }
}
