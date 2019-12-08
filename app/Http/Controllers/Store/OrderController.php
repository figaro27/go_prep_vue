<?php

namespace App\Http\Controllers\Store;

use App\Order;
use App\Bag;
use App\MealOrder;
use App\MealPackageOrder;
use App\MealOrderComponent;
use App\MealOrderAddon;
use App\LineItem;
use App\LineItemOrder;
use App\MealAttachment;
use App\User;
use App\Customer;
use App\Card;
use App\OrderBag;
use App\OrderTransaction;
use App\PurchasedGiftCard;
use Illuminate\Http\Request;
use App\Http\Controllers\Store\StoreController;
use Illuminate\Support\Carbon;
use DB;
use App\Traits\DeliveryDates;

class OrderController extends StoreController
{
    use DeliveryDates;

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
                ->with(['user', 'pickup_location', 'purchased_gift_cards'])
                ->where(['paid' => 1])
                ->get()
            : [];
    }

    public function getUpcomingOrders()
    {
        $fromDate = Carbon::today(
            $this->store->settings->timezone
        )->startOfDay();

        $data = [];
        if ($this->store->has('orders')) {
            $orders = $this->store->orders()->with(['user', 'pickup_location']);

            $orders = $orders->where(function ($query) use ($fromDate) {
                $query
                    ->where(function ($query1) use ($fromDate) {
                        $query1
                            ->where('isMultipleDelivery', 0)
                            ->where('paid', 1);
                        $query1->where(
                            'delivery_date',
                            '>=',
                            $fromDate->format('Y-m-d')
                        );
                    })
                    ->orWhere(function ($query2) use ($fromDate) {
                        $query2
                            ->where('isMultipleDelivery', 1)
                            ->where('paid', 1)
                            ->whereHas('meal_orders', function ($subquery) use (
                                $fromDate
                            ) {
                                $subquery->whereNotNull(
                                    'meal_orders.delivery_date'
                                );
                                $subquery->where(
                                    'meal_orders.delivery_date',
                                    '>=',
                                    $fromDate->format('Y-m-d')
                                );
                            });
                    });
            });

            $data = $orders->get();

            // Newly Added for Table Data
            if ($data) {
                foreach ($data as &$order) {
                }
            }
        }

        return $data;

        /*return $this->store->has('orders')
            ? $this->store
                ->orders()
                ->with(['user', 'pickup_location'])
                ->where(['paid' => 1])
                ->where('delivery_date', '>=', $fromDate)
                ->get()
            : [];*/
    }

    public function getUpcomingOrdersWithoutItems()
    {
        // Optimized orders for Store/Orders & Store/Payments pages
        $fromDate = Carbon::today(
            $this->store->settings->timezone
        )->startOfDay();

        $orders = $this->store->has('orders')
            ? $this->store
                ->orders()
                ->with(['user', 'pickup_location'])
                ->where(['paid' => 1])
                ->where('delivery_date', '>=', $fromDate)
                ->get()
            : [];

        $orders->makeHidden([
            'items',
            'meal_ids',
            'line_items_order',
            'meal_package_items'
        ]);
        return $orders;
    }

    public function getOrdersToday(Request $request)
    {
        $paymentsPage = $request->get('payments');

        $fromDate = Carbon::today(
            $this->store->settings->timezone
        )->startOfDay();

        $date = '';
        if ($paymentsPage) {
            $date = 'created_at';
        } else {
            $date = 'delivery_date';
        }

        $orders = $this->store->has('orders')
            ? $this->store
                ->orders()
                ->with(['user', 'pickup_location'])
                ->where(['paid' => 1])
                ->where($date, '>=', $fromDate)
                ->get()
            : [];

        $orders->makeHidden([
            'items',
            'meal_ids',
            'line_items_order',
            'meal_package_items'
        ]);
        return $orders;
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

        $startDate = Carbon::parse($request->get('start'))->format('Y-m-d');
        $endDate = Carbon::parse($endDate)->format('Y-m-d');

        if ($paymentsPage) {
            return $this->store->has('orders')
                ? $this->store
                    ->orders()
                    ->with(['user', 'pickup_location'])
                    ->where(['paid' => 1])
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<=', $endDate)
                    ->get()
                : [];
        } else {
            if ($this->store->has('orders')) {
                $orders = $this->store
                    ->orders()
                    ->with(['user', 'pickup_location']);

                $orders = $orders->where(function ($query) use (
                    $startDate,
                    $endDate
                ) {
                    $query
                        ->where(function ($query1) use ($startDate, $endDate) {
                            $query1
                                ->where('isMultipleDelivery', 0)
                                ->where('paid', 1);
                            $query1->where('delivery_date', '>=', $startDate);
                            $query1->where('delivery_date', '<=', $endDate);
                        })
                        ->orWhere(function ($query2) use (
                            $startDate,
                            $endDate
                        ) {
                            $query2
                                ->where('isMultipleDelivery', 1)
                                ->where('paid', 1)
                                ->whereHas('meal_orders', function (
                                    $subquery1
                                ) use ($startDate, $endDate) {
                                    $subquery1->whereNotNull(
                                        'meal_orders.delivery_date'
                                    );
                                    $subquery1->where(
                                        'meal_orders.delivery_date',
                                        '>=',
                                        $startDate
                                    );
                                    $subquery1->where(
                                        'meal_orders.delivery_date',
                                        '<=',
                                        $endDate
                                    );
                                });
                        });
                });

                return $orders->get();
            }

            return [];
        }
    }

    public function getOrdersWithDatesWithoutItems(Request $request)
    {
        // Optimized orders for Store/Orders & Store/Payments pages

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

        $orders = $this->store->has('orders')
            ? $this->store
                ->orders()
                ->with(['user', 'pickup_location'])
                ->where(['paid' => 1])
                ->where($date, '>=', $request->get('start'))
                ->where($date, '<=', $endDate)
                ->get()
            : [];

        $orders->makeHidden([
            'items',
            'meal_ids',
            'line_items_order',
            'meal_package_items'
        ]);

        return $orders;
    }

    public function getLatestOrder()
    {
        $orders = $this->store->has('orders') ? $this->store->orders() : [];
        return $orders->orderBy('created_at', 'desc')->first();
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
                'lineItemsOrder',
                'purchased_gift_cards'
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
        $bagItems = $request->get('bag');
        $bag = new Bag($bagItems, $store);
        $couponId = $request->get('coupon_id');
        $couponReduction = $request->get('couponReduction');
        $couponCode = $request->get('couponCode');
        $purchasedGiftCardId = $request->get('purchased_gift_card_id');
        $purchasedGiftCardReduction = $request->get(
            'purchasedGiftCardReduction'
        );
        $deliveryFee = $request->get('deliveryFee');
        $deliveryDate = $request->get('deliveryDate');
        $isMultipleDelivery = (int) $request->get('isMultipleDelivery');
        $pickupLocation = $request->get('pickupLocation');
        $transferTime = $request->get('transferTime');
        $bagTotal = $bag->getTotal() + $request->get('lineItemTotal');
        $subtotal = $request->get('subtotal');
        $preFeePreDiscount = $request->get('subtotal');
        $afterDiscountBeforeFees = $request->get('afterDiscount');
        $processingFee = $request->get('processingFee');
        $mealPlanDiscount = $request->get('mealPlanDiscount');
        $salesTax = $request->get('salesTax');
        $deliveryFee = $request->get('deliveryFee');
        $processingFee = $request->get('processingFee');
        $cashOrder = $request->get('cashOrder');
        $grandTotal = $request->get('grandTotal');
        $adjustedDifference = $request->get('grandTotal') - $order->amount;
        $balance = $request->get('grandTotal') - $order->amount;
        $notes = $request->get('notes');
        $publicOrderNotes = $request->get('publicOrderNotes');
        // $deposit =
        //     (($order->deposit * $order->amount) / 100 / $grandTotal) * 100;
        $originalDeliveryDate = $order->delivery_date;

        $order->notes = $notes;
        $order->publicNotes = $publicOrderNotes;
        $order->delivery_date = $deliveryDate;
        $order->transferTime = $request->get('transferTime');
        $order->adjusted = 1;
        $order->pickup = $request->get('pickup');
        $order->preFeePreDiscount = $preFeePreDiscount;
        $order->mealPlanDiscount = $mealPlanDiscount;
        $order->afterDiscountBeforeFees = $afterDiscountBeforeFees;
        $order->deliveryFee = $deliveryFee;
        $order->processingFee = $processingFee;
        $order->isMultipleDelivery = $isMultipleDelivery;
        $order->salesTax = $salesTax;
        $order->amount = $grandTotal;
        // $order->deposit = $deposit;
        $order->adjustedDifference += $adjustedDifference;
        $order->balance += $balance;
        $order->coupon_id = $couponId;
        $order->couponReduction = $couponReduction;
        $order->couponCode = $couponCode;
        $order->coupon_id = $couponId;
        $order->couponReduction = $couponReduction;
        $order->couponCode = $couponCode;
        $order->purchased_gift_card_id = $purchasedGiftCardId;
        $order->purchasedGiftCardReduction = $purchasedGiftCardReduction;
        $order->pickup_location_id = $pickupLocation;
        $order->transferTime = $transferTime;

        $dailyOrderNumber = 0;
        if (!$isMultipleDelivery) {
            $max = Order::where('store_id', $store->id)
                ->whereDate('delivery_date', $deliveryDate)
                ->max('dailyOrderNumber');
            $dailyOrderNumber = $max + 1;

            if ($originalDeliveryDate != $deliveryDate) {
                $order->dailyOrderNumber = $dailyOrderNumber;
            }
        }

        $order->save();

        $order->meal_orders()->delete();
        $order->meal_package_orders()->delete();
        $order->lineItemsOrder()->delete();
        foreach ($bag->getItems() as $item) {
            $mealOrder = new MealOrder();
            $mealOrder->order_id = $order->id;
            $mealOrder->store_id = $store->id;
            $mealOrder->meal_id = $item['meal']['id'];
            $mealOrder->quantity = $item['quantity'];
            $mealOrder->price = $item['price'] * $item['quantity'];
            if (isset($item['delivery_day']) && $item['delivery_day']) {
                $mealOrder->delivery_date = $this->getDeliveryDateMultipleDelivery(
                    $item['delivery_day']['day'],
                    $isMultipleDelivery
                );
            }
            if (isset($item['size']) && $item['size']) {
                $mealOrder->meal_size_id = $item['size']['id'];
            }
            if (isset($item['special_instructions'])) {
                $mealOrder->special_instructions =
                    $item['special_instructions'];
            }
            if (isset($item['free'])) {
                $mealOrder->free = $item['free'];
            }
            if ($item['meal_package']) {
                $mealOrder->meal_package = $item['meal_package'];
            }

            if (isset($item['meal_package_title'])) {
                $mealOrder->meal_package_title = $item['meal_package_title'];
            }

            if ($item['meal_package'] === true) {
                if (
                    MealPackageOrder::where([
                        'meal_package_id' => $item['meal_package_id'],
                        'meal_package_size_id' => $item['meal_package_size_id'],
                        'order_id' => $order->id
                    ])
                        ->get()
                        ->count() === 0
                ) {
                    $mealPackageOrder = new MealPackageOrder();
                    $mealPackageOrder->store_id = $store->id;
                    $mealPackageOrder->order_id = $order->id;
                    $mealPackageOrder->meal_package_id =
                        $item['meal_package_id'];
                    $mealPackageOrder->meal_package_size_id =
                        $item['meal_package_size_id'];
                    $mealPackageOrder->quantity = $item['package_quantity'];
                    $mealPackageOrder->price = $item['package_price'];
                    if (isset($item['delivery_day']) && $item['delivery_day']) {
                        $mealPackageOrder->delivery_date = $this->getDeliveryDateMultipleDelivery(
                            $item['delivery_day']['day'],
                            $isMultipleDelivery
                        );
                    }
                    $mealPackageOrder->save();

                    $mealOrder->meal_package_order_id = $mealPackageOrder->id;
                } else {
                    $mealOrder->meal_package_order_id = MealPackageOrder::where(
                        [
                            'meal_package_id' => $item['meal_package_id'],
                            'meal_package_size_id' =>
                                $item['meal_package_size_id'],
                            'order_id' => $order->id
                        ]
                    )
                        ->pluck('id')
                        ->first();
                }
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

            $attachments = MealAttachment::where([
                'meal_id' => $item['meal']['id'],
                'meal_size_id' => 0
            ])->get();

            $explicitAttachments = MealAttachment::where([
                'meal_id' => $item['meal']['id'],
                'meal_size_id' => isset($item['size']['id'])
                    ? $item['size']['id']
                    : null,
                'meal_package_id' => isset($item['meal_package_id'])
                    ? $item['meal_package_id']
                    : null,
                'meal_package_size_id' => isset($item['meal_package_size_id'])
                    ? $item['meal_package_size_id']
                    : null
            ])->get();

            if (count($explicitAttachments) > 0) {
                $attachments = $explicitAttachments;
            }
            if ($attachments) {
                foreach ($attachments as $attachment) {
                    $mealOrder = new MealOrder();
                    $mealOrder->order_id = $order->id;
                    $mealOrder->store_id = $store->id;
                    $mealOrder->meal_id = $attachment->attached_meal_id;
                    $mealOrder->meal_size_id =
                        $attachment->attached_meal_size_id;
                    $mealOrder->quantity =
                        $attachment->quantity * $item['quantity'];
                    $mealOrder->attached = 1;
                    $mealOrder->free = 1;
                    $mealOrder->hidden = $attachment->hidden;
                    if (isset($item['delivery_day']) && $item['delivery_day']) {
                        $mealOrder->delivery_date = $this->getDeliveryDateMultipleDelivery(
                            $item['delivery_day']['day'],
                            $isMultipleDelivery
                        );
                    }
                    $mealOrder->save();
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

        if ($bagItems && count($bagItems) > 0) {
            OrderBag::where('order_id', (int) $order->id)->delete();

            foreach ($bagItems as $bagItem) {
                $orderBag = new OrderBag();
                $orderBag->order_id = (int) $order->id;
                $orderBag->bag = json_encode($bagItem);
                $orderBag->save();
            }
        }

        // Send email to store only if it's not a manual order. Subject to change in future.
        if ($order->manual === 0) {
            try {
                $store->sendNotification('adjusted_order', [
                    'order' => $order ?? null,
                    'pickup' => $order->pickup ?? null
                ]);
            } catch (\Exception $e) {
            }
        }

        // Send email to customer
        $customerUser = User::where('id', $order->user_id)->first();
        if ($request->get('emailCustomer')) {
            try {
                $customerUser->sendNotification('adjusted_order', [
                    'order' => $order ?? null,
                    'pickup' => $pickup ?? null
                ]);
            } catch (\Exception $e) {
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

    public function charge(Request $request)
    {
        $orderId = $request->get('orderId');
        $chargeAmount = $request->get('chargeAmount');
        $order = Order::where('id', $orderId)->first();
        $cashOrder = $order->cashOrder;
        $store = $this->store;
        $application_fee = $store->settings->application_fee;
        $applyToBalance = $request->get('applyToBalance');

        $user = User::where('id', $order->user_id)->first();
        $customer = Customer::where('id', $order->customer_id)->first();

        if (!$cashOrder) {
            $cardId = $order->card_id;
            $card = Card::where('id', $cardId)->first();
        }

        if (!$cashOrder) {
            $storeSource = \Stripe\Source::create(
                [
                    "customer" => $user->stripe_id,
                    "original_source" => $card->stripe_id,
                    "usage" => "single_use"
                ],
                ["stripe_account" => $store->settings->stripe_id]
            );

            $charge = \Stripe\Charge::create(
                [
                    "amount" => round(100 * $chargeAmount),
                    "currency" => "usd",
                    "source" => $storeSource,
                    // Change to "application_fee_amount" as per Stripe's updates
                    "application_fee" => round($chargeAmount * $application_fee)
                ],
                ["stripe_account" => $store->settings->stripe_id]
            );
        }
        $order->chargedAmount += $chargeAmount;

        if ($applyToBalance) {
            $order->balance -= $chargeAmount;
        }
        $order->save();

        $order_transaction = new OrderTransaction();
        $order_transaction->order_id = $order->id;
        $order_transaction->store_id = $store->id;
        $order_transaction->user_id = $user->id;
        $order_transaction->customer_id = $customer->id;
        $order_transaction->type = 'charge';
        if (!$cashOrder) {
            $order_transaction->stripe_id = $charge->id;
            $order_transaction->card_id = $cardId;
        } else {
            $order_transaction->stripe_id = null;
            $order_transaction->card_id = null;
        }
        $order_transaction->amount = $chargeAmount;
        $order_transaction->applyToBalance = $applyToBalance;
        $order_transaction->save();

        return 'Charged $' . $chargeAmount;
    }

    public function refundOrder(Request $request)
    {
        $order = Order::where('id', $request->get('orderId'))->first();
        $store = $this->store;
        $user = User::where('id', $order->user_id)->first();
        $customer = Customer::where('id', $order->customer_id)->first();
        $applyToBalance = $request->get('applyToBalance');

        $originalAmount = $order->originalAmount;
        $chargedAmount = $order->chargedAmount;
        $totalCharged = $originalAmount + $chargedAmount;
        $refundAmount = $request->get('refundAmount');
        if ($refundAmount === null) {
            $refundAmount = $totalCharged;
        }
        if ($refundAmount > $totalCharged) {
            return 1;
        }

        $difference = 0;

        if ($refundAmount > $originalAmount) {
            $difference = $refundAmount - $originalAmount;
            $refundAmount = $originalAmount;
        }

        $card = Card::where('id', $order->card_id)->first();
        $refund = \Stripe\Refund::create(
            [
                'charge' => $order->stripe_id,
                'amount' => $refundAmount * 100
            ],
            ["stripe_account" => $this->store->settings->stripe_id]
        );

        $order_transaction = new OrderTransaction();
        $order_transaction->order_id = $order->id;
        $order_transaction->store_id = $store->id;
        $order_transaction->user_id = $user->id;
        $order_transaction->customer_id = $customer->id;
        $order_transaction->type = 'refund';
        $order_transaction->stripe_id = $refund->id;
        $order_transaction->card_id = $card ? $card->id : null;
        $order_transaction->amount = $refundAmount;
        $order_transaction->applyToBalance = $applyToBalance;
        $order_transaction->save();

        if ($difference > 0) {
            $charges = OrderTransaction::where([
                'order_id' => $order->id,
                'type' => 'charge'
            ])->get();
            foreach ($charges as $charge) {
                if ($difference <= $charge->amount) {
                    $card = Card::where('id', $charge->card_id)->first();
                    $refund = \Stripe\Refund::create(
                        [
                            'charge' => $charge->stripe_id,
                            'amount' => $difference * 100
                        ],
                        ["stripe_account" => $this->store->settings->stripe_id]
                    );

                    $order_transaction = new OrderTransaction();
                    $order_transaction->order_id = $order->id;
                    $order_transaction->store_id = $store->id;
                    $order_transaction->user_id = $user->id;
                    $order_transaction->customer_id = $customer->id;
                    $order_transaction->type = 'refund';
                    $order_transaction->stripe_id = $refund->id;
                    $order_transaction->card_id = $card ? $card->id : null;
                    $order_transaction->amount = $difference;
                    $order_transaction->applyToBalance = $applyToBalance;
                    $order_transaction->save();

                    $difference += $charge->amount;
                }
            }
        }

        if ($applyToBalance) {
            $order->balance += $request->get('refundAmount');
        }
        $order->refundedAmount += $request->get('refundAmount');
        $order->save();

        return 'Refunded $' . $request->get('refundAmount');
    }

    public function settleBalance(Request $request)
    {
        $order = Order::where('id', $request->get('orderId'))->first();
        $order->balance = 0;
        $order->save();
    }

    public function voidOrder(Request $request)
    {
        $order = Order::where('id', $request->get('orderId'))->first();
        if ($order->voided === 0) {
            $order->voided = 1;
            $order->save();
            return 'Order voided.';
        } else {
            $order->voided = 0;
            $order->save();
            return 'Order unvoided.';
        }
    }

    public function updateBalance(Request $request)
    {
        $order = Order::where('id', $request->get('id'))->first();
        $order->balance = $request->get('balance');
        $order->save();
    }

    public function emailCustomerReceipt(Request $request)
    {
        // Send email to customer
        $order = Order::where('id', $request->get('id'))->first();
        $customerUser = User::where('id', $order->user_id)->first();
        try {
            $customerUser->sendNotification('new_order', [
                'order' => $order ?? null,
                'pickup' => $order->pickup ?? null
            ]);
        } catch (\Exception $e) {
        }
    }

    public function getLineItemOrders($order_id)
    {
        return LineItemOrder::where('order_id', $order_id)
            ->get()
            ->map(function ($lineItemOrder) {
                return [
                    'price' => $lineItemOrder->price,
                    'quantity' => $lineItemOrder->quantity,
                    'title' => $lineItemOrder->title,
                    'production_group_id' => $lineItemOrder->production_group_id
                ];
            });
    }
}
