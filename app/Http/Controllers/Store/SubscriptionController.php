<?php

namespace App\Http\Controllers\Store;
use Illuminate\Support\Facades\Mail;
use App\Customer;
use App\Mail\Store\CancelledSubscription;
use App\Bag;
use App\MealOrder;
use App\MealOrderComponent;
use App\MealOrderAddon;
use App\MealSubscription;
use App\MealSubscriptionComponent;
use App\MealSubscriptionAddon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Store;
use App\SubscriptionBag;
use App\MealPackage;
use App\MealPackageSize;
use App\MealPackageSubscription;
use App\MealPackageOrder;
use App\Subscription;
use App\PurchasedGiftCard;

class SubscriptionController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subscriptions = $this->store
            ->subscriptions()
            ->where('status', '!=', 'cancelled')
            ->with(['user:id', 'pickup_location'])
            ->orderBy('created_at')
            ->get();

        $subscriptions->makeHidden([
            'latest_order',
            'latest_paid_order',
            'latest_unpaid_order',
            'meal_ids',
            'meal_quantities',
            'store',
            'orders',
            'items',
            'meal_package_items'
        ]);

        return $subscriptions;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Subscription::where('id', $id)
            ->with(['pickup_location', 'user'])
            ->first();
    }

    /**
     * Cancel
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $sub = $this->store->subscriptions()->find($id);

        if (!$sub) {
            return response()->json(
                [
                    'error' => 'Subscription not found'
                ],
                404
            );
        }

        try {
            $sub->cancel();
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to cancel Subscription'
                ],
                500
            );
        }
    }

    public function pause(Request $request)
    {
        $id = $request->get('id');
        $sub = $this->store->subscriptions()->find($id);

        if (!$sub) {
            return response()->json(
                [
                    'error' => 'Subscription not found'
                ],
                404
            );
        }

        try {
            $sub->pause();
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to pause Subscription'
                ],
                500
            );
        }
    }

    public function resume(Request $request)
    {
        $id = $request->get('id');
        $sub = $this->store->subscriptions()->find($id);

        if ($sub->store->settings->open === false) {
            return response()->json(
                [
                    'error' =>
                        'This store is currently closed. Please try again when they re-open.'
                ],
                404
            );
        }

        if (!$sub) {
            return response()->json(
                [
                    'error' => 'Subscription not found'
                ],
                404
            );
        }

        try {
            $sub->resume();
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to resume Subscription'
                ],
                500
            );
        }
    }

    public function updateMeals(Request $request)
    {
        try {
            $id = intval($request->get('subscriptionId'));
            $sub = $this->store->subscriptions()->find($id);

            if (!$sub) {
                return response()->json(
                    [
                        'error' => 'Subscription not found.'
                    ],
                    404
                );
            }
            $store = $sub->store;

            if (!$sub->cashOrder) {
                try {
                    $subscription = \Stripe\Subscription::retrieve(
                        'sub_' . $sub->stripe_id,
                        ['stripe_account' => $store->settings->stripe_id]
                    );
                } catch (\Exception $e) {
                    return response()->json(
                        [
                            'error' =>
                                'Subscription not found at payment gateway.'
                        ],
                        404
                    );
                }
            }

            $user = auth('api')->user();
            $storeId = $request->get('store_id');
            $store = Store::with(['settings', 'storeDetail'])->findOrFail(
                $storeId
            );
            $storeName = strtolower($store->storeDetail->name);
            $bagItems = $request->get('bag');
            $bag = new Bag($request->get('bag'), $store);

            if (count($bagItems) == 0) {
                return response()->json(
                    [
                        'message' =>
                            'Your subscription must have at least one item.'
                    ],
                    400
                );
            }

            // Don't allow gift cards on subscription adjustments
            foreach ($bag->getItems() as $item) {
                if (
                    isset($item['meal']) &&
                    isset($item['meal']['gift_card']) &&
                    $item['meal']['gift_card'] === true
                ) {
                    return response()->json(
                        [
                            'message' =>
                                'Gift cards are not allowed on subscriptions since the subscription will renew and charge the customer for this gift card again next week. Please create a separate one time order to purchase a gift card.'
                        ],
                        400
                    );
                }
            }

            $weeklyPlan = $request->get('plan');
            $pickup = $request->get('pickup');
            $shipping = $request->get('shipping');
            $deliveryDay = $sub->delivery_day;
            $couponId = $request->get('coupon_id');
            $couponReduction = $request->get('couponReduction');
            $couponCode = $request->get('couponCode');
            $deliveryFee = $request->get('deliveryFee');
            $pickupLocation = $request->get('pickupLocation');
            //$stripeToken = $request->get('token');

            $application_fee = $store->settings->application_fee;
            $gratuity = $request->get('gratuity');
            $coolerDeposit = $request->get('coolerDeposit');
            $total = $request->get('subtotal');
            $subtotal = $request->get('subtotal');
            $afterDiscountBeforeFees = $request->get('afterDiscount');
            $preFeePreDiscount = $subtotal;
            $deposit = $request->get('deposit') / 100;

            $processingFee = $request->get('processingFee');
            $mealPlanDiscount = $request->get('mealPlanDiscount');
            $salesTax = $request->get('salesTax');

            $promotionReduction = $request->get('promotionReduction');
            $appliedReferralId = $request->get('applied_referral_id');
            $referralReduction = $request->get('referralReduction');
            $pointsReduction = $request->get('pointsReduction');
            $purchasedGiftCardId = $request->get('purchased_gift_card_id');
            $purchasedGiftCardReduction = $request->get(
                'purchasedGiftCardReduction'
            )
                ? $request->get('purchasedGiftCardReduction')
                : 0;
            // if ($store->settings->applyMealPlanDiscount && $weeklyPlan) {
            //     $discount = $store->settings->mealPlanDiscount / 100;
            //     $mealPlanDiscount = $total * $discount;
            //     $total -= $mealPlanDiscount;
            //     $afterDiscountBeforeFees = $total;
            // }

            // if ($store->settings->applyDeliveryFee) {
            //     $total += $deliveryFee;
            // }

            // if ($store->settings->applyProcessingFee) {
            //     if ($store->settings->processingFeeType === 'flat') {
            //         $processingFee += $store->settings->processingFee;
            //     } elseif ($store->settings->processingFeeType === 'percent') {
            //         $processingFee +=
            //             ($store->settings->processingFee / 100) * $subtotal;
            //     }

            //     $total += $processingFee;
            // }

            // if ($couponId != null) {
            //     $total -= $couponReduction;
            // }

            $customerId = $request->get('customer');
            $customer = Customer::where('id', $customerId)->first();

            // $total += $salesTax;
            $total = $request->get('grandTotal');

            $cashOrder = $request->get('cashOrder');
            if ($cashOrder) {
                $cardId = null;
                $card = null;
            }

            // Delete existing stripe plan
            if (!$sub->cashOrder) {
                try {
                    $plan = \Stripe\Plan::retrieve($sub->stripe_plan, [
                        'stripe_account' => $sub->store->settings->stripe_id
                    ]);
                    $plan->delete();
                } catch (\Exception $e) {
                    return response()->json(
                        [
                            'error' =>
                                'Failed to update subscription. Please get in touch'
                        ],
                        500
                    );
                }

                // Create stripe plan with new pricing
                $plan = \Stripe\Plan::create(
                    [
                        "amount" => round($total * 100),
                        "interval" => $sub->interval,
                        "product" => [
                            "name" =>
                                $sub->interval .
                                "ly subscription (" .
                                $store->storeDetail->name .
                                ")"
                        ],
                        "currency" => $store->settings->currency
                    ],
                    ['stripe_account' => $store->settings->stripe_id]
                );

                // Assign plan to stripe subscription
                \Stripe\Subscription::update(
                    $subscription->id,
                    [
                        'cancel_at_period_end' => false,
                        'items' => [
                            [
                                'id' => $subscription->items->data[0]->id,
                                'plan' => $plan->id
                            ]
                        ],
                        'prorate' => false
                    ],
                    ['stripe_account' => $store->settings->stripe_id]
                );

                // Assign new plan ID to subscription
                $sub->stripe_plan = $plan->id;

                // If the current subscription is in draft state (within 1 hour before renewal, add line item to current invoice)
                $invoice = \Stripe\Invoice::retrieve(
                    $subscription->latest_invoice,
                    [
                        'stripe_account' => $store->settings->stripe_id
                    ]
                );
                if ($invoice->status === 'draft') {
                    $invoiceItem = \Stripe\InvoiceItem::create(
                        [
                            'invoice' => $invoice->id,
                            'customer' => $subscription->customer,
                            'amount' => round(($total - $sub->amount) * 100),
                            'currency' => $this->store->settings->currency,
                            'description' =>
                                'Subscription updated in draft state.'
                        ],
                        ['stripe_account' => $store->settings->stripe_id]
                    );
                }
            }

            // Update meals in subscription
            MealSubscription::where('subscription_id', $sub->id)->delete();
            MealPackageSubscription::where(
                'subscription_id',
                $sub->id
            )->delete();
            foreach ($bag->getItems() as $item) {
                $mealSub = new MealSubscription();
                $mealSub->subscription_id = $sub->id;
                $mealSub->store_id = $store->id;
                $mealSub->meal_id = $item['meal']['id'];
                $mealSub->quantity = $item['quantity'];
                $mealSub->price = $item['price'] * $item['quantity'];
                if (isset($item['free'])) {
                    $mealSub->free = $item['free'];
                }
                if (isset($item['size']) && $item['size']) {
                    $mealSub->meal_size_id = $item['size']['id'];
                }

                if ($item['meal_package']) {
                    $mealSub->meal_package = $item['meal_package'];
                }
                if (!$item['meal_package']) {
                    $mealSub->customTitle = isset($item['customTitle'])
                        ? $item['customTitle']
                        : null;
                    $mealSub->customSize = isset($item['customSize'])
                        ? $item['customSize']
                        : null;
                }
                if ($item['meal_package'] === true) {
                    if (
                        MealPackageSubscription::where([
                            'meal_package_id' => $item['meal_package_id'],
                            'meal_package_size_id' =>
                                $item['meal_package_size_id'],
                            'subscription_id' => $sub->id,
                            'customTitle' => isset($item['customTitle'])
                                ? $item['customTitle']
                                : null,
                            'mappingId' => isset($item['mappingId'])
                                ? $item['mappingId']
                                : null
                        ])
                            ->get()
                            ->count() === 0
                    ) {
                        $mealPackageSubscription = new MealPackageSubscription();
                        $mealPackageSubscription->store_id = $store->id;
                        $mealPackageSubscription->subscription_id = $sub->id;
                        $mealPackageSubscription->meal_package_id =
                            $item['meal_package_id'];
                        $mealPackageSubscription->meal_package_size_id =
                            $item['meal_package_size_id'];
                        $mealPackageSubscription->quantity =
                            $item['package_quantity'];
                        $mealPackageSubscription->price =
                            $item['package_price'];
                        if (
                            isset($item['delivery_day']) &&
                            $item['delivery_day']
                        ) {
                            $mealPackageSubscription->delivery_date =
                                $item['delivery_day']['day_friendly'];
                        }
                        $mealPackageSubscription->customTitle = isset(
                            $item['customTitle']
                        )
                            ? $item['customTitle']
                            : null;
                        $mealPackageSubscription->customSize = isset(
                            $item['customSize']
                        )
                            ? $item['customSize']
                            : null;
                        $mealPackageSubscription->mappingId = isset(
                            $item['mappingId']
                        )
                            ? $item['mappingId']
                            : null;
                        $mealPackageSubscription->save();

                        $mealSub->meal_package_subscription_id =
                            $mealPackageSubscription->id;
                    } else {
                        $mealSub->meal_package_subscription_id = MealPackageSubscription::where(
                            [
                                'meal_package_id' => $item['meal_package_id'],
                                'meal_package_size_id' =>
                                    $item['meal_package_size_id'],
                                'subscription_id' => $sub->id,
                                'mappingId' => isset($item['mappingId'])
                                    ? $item['mappingId']
                                    : null
                            ]
                        )
                            ->pluck('id')
                            ->first();
                    }
                }

                if (isset($item['delivery_day']) && $item['delivery_day']) {
                    $mealSub->delivery_date =
                        $item['delivery_day']['day_friendly'];
                }

                $mealSub->save();

                if (isset($item['components']) && $item['components']) {
                    foreach ($item['components'] as $componentId => $choices) {
                        foreach ($choices as $optionId) {
                            MealSubscriptionComponent::create([
                                'meal_subscription_id' => $mealSub->id,
                                'meal_component_id' => $componentId,
                                'meal_component_option_id' => $optionId
                            ]);
                        }
                    }
                }

                if (isset($item['addons']) && $item['addons']) {
                    foreach ($item['addons'] as $addonId) {
                        MealSubscriptionAddon::create([
                            'meal_subscription_id' => $mealSub->id,
                            'meal_addon_id' => $addonId
                        ]);
                    }
                }
            }

            $subscriptionBags = SubscriptionBag::where(
                'subscription_id',
                $sub->id
            )->get();
            foreach ($subscriptionBags as $subscriptionBag) {
                $subscriptionBag->delete();
            }

            if ($bagItems && count($bagItems) > 0) {
                foreach ($bagItems as $bagItem) {
                    $subscriptionBag = new SubscriptionBag();
                    $subscriptionBag->subscription_id = (int) $sub->id;
                    $subscriptionBag->bag = json_encode($bagItem);
                    $subscriptionBag->save();
                }
            }

            // Update subscription pricing
            // $sub->preFeePreDiscount = $preFeePreDiscount;
            // $sub->mealPlanDiscount = $mealPlanDiscount;
            // $sub->afterDiscountBeforeFees = $afterDiscountBeforeFees;
            // $sub->processingFee = $processingFee;
            // $sub->deliveryFee = $deliveryFee;
            // $sub->salesTax = $salesTax;
            // $sub->amount = $total;
            // $sub->save();

            $sub->store_id = $store->id;
            $sub->preFeePreDiscount = $preFeePreDiscount;
            $sub->mealPlanDiscount = $mealPlanDiscount;
            $sub->afterDiscountBeforeFees = $afterDiscountBeforeFees;
            $sub->deliveryFee = $deliveryFee;
            $sub->processingFee = $processingFee;
            $sub->salesTax = $salesTax;
            $sub->gratuity = $gratuity;
            $sub->coolerDeposit = $coolerDeposit;
            $sub->amount = $total;
            $sub->pickup = $request->get('pickup', 0);
            $sub->shipping = $shipping;
            $sub->delivery_day = $deliveryDay;
            $sub->coupon_id = $couponId;
            $sub->couponReduction = $couponReduction;
            $sub->couponCode = $couponCode;
            $sub->pickup_location_id = $pickupLocation;
            $sub->referralReduction = $referralReduction;
            $sub->purchased_gift_card_id = $purchasedGiftCardId;
            $sub->purchasedGiftCardReduction = $purchasedGiftCardReduction;
            $sub->promotionReduction = $promotionReduction;
            $sub->pointsReduction = $pointsReduction;
            $sub->store_updated = Carbon::now(
                $this->store->settings->timezone
            )->toDateTimeString();
            $sub->save();

            // Update future orders IF cutoff hasn't passed yet
            $futureOrders = $sub
                ->orders()
                ->where([['fulfilled', 0], ['paid', 0]])
                ->whereDate('delivery_date', '>=', Carbon::now())
                ->get();

            foreach ($futureOrders as $order) {
                // Cutoff already passed. Missed your chance bud!
                if ($order->cutoff_passed) {
                    continue;
                }

                // Update order pricing
                $order->preFeePreDiscount = $preFeePreDiscount;
                $order->mealPlanDiscount = $mealPlanDiscount;
                $order->afterDiscountBeforeFees = $afterDiscountBeforeFees;
                $order->processingFee = $processingFee;
                $order->deliveryFee = $deliveryFee;
                $order->couponReduction = $couponReduction;
                $order->couponCode = $couponCode;
                $order->salesTax = $salesTax;
                $order->referralReduction = $referralReduction;
                $order->purchased_gift_card_id = $purchasedGiftCardId;
                $order->purchasedGiftCardReduction = $purchasedGiftCardReduction;
                $order->promotionReduction = $promotionReduction;
                $order->pointsReduction = $pointsReduction;
                $order->gratuity = $gratuity;
                $order->coolerDeposit = $coolerDeposit;
                $order->amount = $total;
                $order->save();

                // Replace order meals && meal packages
                $order->meal_orders()->delete();

                $mealPackageOrders = MealPackageOrder::where(
                    'order_id',
                    $order->id
                )->get();
                foreach ($mealPackageOrders as $mealPackageOrder) {
                    $mealPackageOrder->delete();
                }

                foreach ($bag->getItems() as $item) {
                    $mealOrder = new MealOrder();
                    $mealOrder->order_id = $order->id;
                    $mealOrder->store_id = $store->id;
                    $mealOrder->meal_id = $item['meal']['id'];
                    $mealOrder->quantity = $item['quantity'];
                    $mealOrder->price = $item['price'] * $item['quantity'];
                    if (isset($item['delivery_day']) && $item['delivery_day']) {
                        $mealOrder->delivery_date =
                            $item['delivery_day']['day_friendly'];
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
                        $mealOrder->meal_package_variation = isset(
                            $item['meal_package_variation']
                        )
                            ? $item['meal_package_variation']
                            : 0;
                    }

                    if (isset($item['meal_package_title'])) {
                        $mealOrder->meal_package_title =
                            $item['meal_package_title'];
                    }
                    $mealOrder->save();

                    if (isset($item['components']) && $item['components']) {
                        foreach (
                            $item['components']
                            as $componentId => $choices
                        ) {
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

                    if ($item['meal_package']) {
                        $mealOrder->meal_package = $item['meal_package'];
                        $mealOrder->meal_package_variation = isset(
                            $item['meal_package_variation']
                        )
                            ? $item['meal_package_variation']
                            : 0;
                    }

                    if (isset($item['meal_package_title'])) {
                        $mealOrder->meal_package_title =
                            $item['meal_package_title'];
                    }

                    if ($item['meal_package'] === true) {
                        if (
                            MealPackageOrder::where([
                                'meal_package_id' => $item['meal_package_id'],
                                'meal_package_size_id' =>
                                    $item['meal_package_size_id'],
                                'order_id' => $order->id,
                                'delivery_date' =>
                                    $item['delivery_day']['day_friendly'],
                                'customTitle' => $item['customTitle'],
                                'mappingId' => isset($item['mappingId'])
                                    ? $item['mappingId']
                                    : null
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
                            $mealPackageOrder->quantity =
                                $item['package_quantity'];
                            // $mealPackageOrder->price =
                            //     $item['meal_package_size_id'] !== null
                            //         ? MealPackageSize::where(
                            //             'id',
                            //             $item['meal_package_size_id']
                            //         )
                            //             ->pluck('price')
                            //             ->first()
                            //         : MealPackage::where(
                            //             'id',
                            //             $item['meal_package_id']
                            //         )
                            //             ->pluck('price')
                            //             ->first();
                            $mealPackageOrder->price = $item['package_price'];
                            if (
                                isset($item['delivery_day']) &&
                                $item['delivery_day']
                            ) {
                                $mealPackageOrder->delivery_date =
                                    $item['delivery_day']['day_friendly'];
                            }
                            $mealPackageOrder->customTitle =
                                $item['customTitle'];
                            $mealPackageOrder->mappingId = isset(
                                $item['mappingId']
                            )
                                ? $item['mappingId']
                                : null;
                            $mealPackageOrder->save();
                        }
                    }
                    if ($item['meal_package'] === true && $mealPackageOrder) {
                        $mealOrder->meal_package_order_id =
                            $mealPackageOrder->id;
                        $mealOrder->update();
                    }
                }
            }
            if (isset($purchasedGiftCardId)) {
                $purchasedGiftCard = PurchasedGiftCard::where(
                    'id',
                    $purchasedGiftCardId
                )->first();
                $purchasedGiftCard->balance -= $purchasedGiftCardReduction;
                $purchasedGiftCard->update();
            }
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => $e->getMessage()
                ],
                400
            );
        }
    }

    public function subscriptionBag($subscription_id)
    {
        $subscription_bags = SubscriptionBag::where(
            'subscription_id',
            $subscription_id
        )
            ->orderBy('id', 'asc')
            ->get();
        $data = [];

        if ($subscription_bags) {
            foreach ($subscription_bags as $subscription_bag) {
                $data[] = json_decode($subscription_bag->bag);
            }
        }

        return [
            'subscription_bags' => $data
        ];
    }
}
