<?php

namespace App\Http\Controllers\User;

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
use App\Customer;
use App\SubscriptionBag;
use App\MealPackage;
use App\MealPackageSize;
use App\MealPackageSubscription;
use App\MealPackageOrder;
use App\Subscription;

class SubscriptionController extends UserController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subscriptions = $this->user
            ->subscriptions()
            ->where('status', 'active')
            ->with(['orders', 'pickup_location'])
            ->get();

        $subscriptions->makeHidden([
            'latest_order',
            'latest_paid_order',
            'latest_unpaid_order',
            'next_order',
            'meal_ids',
            'meal_quantities',
            'store',
            'orders',
            'items',
            'meal_package_items'
        ]);

        return $subscriptions;
    }

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
        $sub = $this->user->subscriptions()->find($id);

        if (!$sub) {
            return response()->json(
                [
                    'error' => 'Subscription not found'
                ],
                404
            );
        }

        if ($sub->monthlyPrepay) {
            if ($sub->weekCount % 4 === 0) {
                $sub->cancel();
            } else {
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
            return;
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

    /**
     * Pause
     *
     * @return \Illuminate\Http\Response
     */
    public function pause($id)
    {
        $sub = $this->user->subscriptions()->find($id);

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

    /**
     * Pause
     *
     * @return \Illuminate\Http\Response
     */
    public function resume($id)
    {
        $sub = $this->user->subscriptions()->find($id);

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

    public function getSubscriptionPickup($id)
    {
        $sub = $this->user->subscriptions()->find($id);

        if (!$sub) {
            return response()->json(
                [
                    'error' => 'Subscription not found'
                ],
                404
            );
        }

        return $sub->pickup;
    }

    /**
     * Update meals
     *
     * @return \Illuminate\Http\Response
     */
    public function updateMeals(Request $request, $id)
    {
        $id = intval($request->get('subscriptionId'));
        $sub = $this->user->subscriptions()->find($id);

        if (!$sub) {
            return response()->json(
                [
                    'error' => 'Subscription not found'
                ],
                404
            );
        }
        $store = $sub->store;

        try {
            $subscription = \Stripe\Subscription::retrieve(
                'sub_' . $sub->stripe_id,
                ['stripe_account' => $store->settings->stripe_id]
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Subscription not found at payment gateway'
                ],
                404
            );
        }

        $user = auth('api')->user();
        $storeId = $request->get('store_id');
        $store = Store::with(['settings', 'storeDetail'])->findOrFail($storeId);
        $storeName = strtolower($store->storeDetail->name);

        $bagItems = $request->get('bag');
        $bag = new Bag($request->get('bag'), $store);

        if (count($bagItems) == 0) {
            return response()->json(
                [
                    'message' =>
                        'Your subscription must have at least one item. If you are trying to pause the subscription, please cancel the subscription and create a new one when ready. We are adding subscription pausing soon.'
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
                            'Gift cards are not allowed on subscriptions since the subscription will renew and charge you for this gift card again next week. Please contact the store if you would like to purchase a one time gift card.'
                    ],
                    400
                );
            }
        }

        $weeklyPlan = $request->get('plan');
        $pickup = $request->get('pickup');
        $deliveryDay = $sub->delivery_day;
        $couponId = $request->get('coupon_id');
        $couponReduction = $request->get('couponReduction');
        $couponCode = $request->get('couponCode');
        $deliveryFee = $request->get('deliveryFee');
        $gratuity = $request->get('gratuity');
        $pickupLocation = $request->get('pickupLocation');
        //$stripeToken = $request->get('token');

        $application_fee = $store->settings->application_fee;
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
                "interval" => "week",
                "product" => [
                    "name" =>
                        "Weekly subscription (" .
                        $store->storeDetail->name .
                        ")"
                ],
                "currency" => "usd"
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

        // Update meals in subscription
        MealSubscription::where('subscription_id', $sub->id)->delete();
        MealPackageSubscription::where('subscription_id', $sub->id)->delete();
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
                        'meal_package_size_id' => $item['meal_package_size_id'],
                        'subscription_id' => $sub->id,
                        'customTitle' => isset($item['customTitle'])
                            ? $item['customTitle']
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
                    $mealPackageSubscription->price = $item['package_price'];
                    if (isset($item['delivery_day']) && $item['delivery_day']) {
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
                    $mealPackageSubscription->save();

                    $mealSub->meal_package_subscription_id =
                        $mealPackageSubscription->id;
                } else {
                    $mealSub->meal_package_subscription_id = MealPackageSubscription::where(
                        [
                            'meal_package_id' => $item['meal_package_id'],
                            'meal_package_size_id' =>
                                $item['meal_package_size_id'],
                            'subscription_id' => $sub->id
                        ]
                    )
                        ->pluck('id')
                        ->first();
                }
            }
            if (isset($item['delivery_day']) && $item['delivery_day']) {
                $mealSub->delivery_date = $item['delivery_day']['day_friendly'];
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
        $sub->amount = $total;
        $sub->pickup = $request->get('pickup', 0);
        $sub->delivery_day = $deliveryDay;
        $sub->coupon_id = $couponId;
        $sub->couponReduction = $couponReduction;
        $sub->couponCode = $couponCode;
        $sub->pickup_location_id = $pickupLocation;
        $sub->referralReduction = $referralReduction;
        $sub->promotionReduction = $promotionReduction;
        $sub->pointsReduction = $pointsReduction;
        $sub->customer_updated = Carbon::now(
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
            $order->gratuity = $gratuity;
            $order->couponReduction = $couponReduction;
            $order->couponCode = $couponCode;
            $order->salesTax = $salesTax;
            $order->referralReduction = $referralReduction;
            $order->promotionReduction = $promotionReduction;
            $order->pointsReduction = $pointsReduction;
            $order->amount = $total;
            $order->save();

            // Replace order meals
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
                        $mealPackageOrder->save();
                    }
                }
                if ($item['meal_package'] === true && $mealPackageOrder) {
                    $mealOrder->meal_package_order_id = $mealPackageOrder->id;
                    $mealOrder->update();
                }
            }
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
