<?php

namespace App\Http\Controllers\User;

use App\Bag;
use App\Http\Controllers\User\UserController;
use App\Mail\Customer\MealPlan;
use App\Mail\Customer\NewOrder;
use App\MealOrder;
use App\MealOrderComponent;
use App\MealSubscriptionComponent;
use App\MealOrderAddon;
use App\MealSubscriptionAddon;
use App\MealSubscription;
use App\MealAttachment;
use App\Order;
use App\OrderTransaction;
use App\Store;
use App\StoreDetail;
use App\Subscription;
use App\Coupon;
use App\MealPackageOrder;
use App\MealPackageSubscription;
use App\Billing\Billing;
use App\Billing\Constants;
use App\Billing\Charge;
use App\Billing\Authorize;
use Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use DB;

class CheckoutController extends UserController
{
    public function checkout(\App\Http\Requests\CheckoutRequest $request)
    {
        $user = auth('api')->user();
        $storeId = $request->get('store_id');
        $store = Store::with([
            'settings',
            'modules',
            'storeDetail'
        ])->findOrFail($storeId);
        $storeSettings = $store->settings;

        $bag = new Bag($request->get('bag'), $store);
        $weeklyPlan = $request->get('plan');
        $pickup = $request->get('pickup');
        $deliveryDay = $request->get('delivery_day');
        $couponId = $request->get('coupon_id');
        $couponReduction = $request->get('couponReduction');
        $couponCode = $request->get('couponCode');
        $deliveryFee = $request->get('deliveryFee');
        $pickupLocation = $request->get('pickupLocation');
        $transferTime = $request->get('transferTime');
        //$stripeToken = $request->get('token');
        $deposit = 1;

        $cashOrder = $request->get('cashOrder');
        if ($cashOrder) {
            $cardId = null;
            $card = null;
            $gateway = Constants::GATEWAY_CASH;
        } else {
            $cardId = $request->get('card_id');
            $card = $this->user->cards()->findOrFail($cardId);
            $gateway = $card->payment_gateway;
        }

        $application_fee = $storeSettings->application_fee;
        $total = $request->get('subtotal');
        $subtotal = $request->get('subtotal');
        $preFeePreDiscount = $request->get('subtotal');
        $afterDiscountBeforeFees = $request->get('afterDiscount');

        $processingFee = $request->get('processingFee');
        $mealPlanDiscount = $request->get('mealPlanDiscount');
        $salesTax = $request->get('salesTax');

        $max = Order::where('store_id', $storeId)
            ->whereDate('delivery_date', $deliveryDay)
            ->max('dailyOrderNumber');

        if ($max) {
            $dailyOrderNumber = $max + 1;
        } else {
            $dailyOrderNumber = 1;
        }

        // if ($store->settings->applyMealPlanDiscount && $weeklyPlan) {
        //     $discount = $store->settings->mealPlanDiscount / 100;
        //     $mealPlanDiscount = $total * $discount;
        //     $total -= $mealPlanDiscount;
        //     $afterDiscountBeforeFees = $total;
        // }

        // if ($storeSettings->applyDeliveryFee) {
        //     $total += $deliveryFee;
        // }

        // if ($storeSettings->applyProcessingFee) {
        //     if ($storeSettings->processingFeeType === 'flat') {
        //         $processingFee += $storeSettings->processingFee;
        //     } elseif ($storeSettings->processingFeeType === 'percent') {
        //         $processingFee +=
        //             ($storeSettings->processingFee / 100) * $subtotal;
        //     }

        //     $total += $processingFee;
        // }

        // if ($couponId != null) {
        //     // $coupon = Coupon::where('id', $couponId)->first();
        //     // $couponReduction = 0;
        //     // if ($coupon->type === 'flat') {
        //     //     $couponReduction = $coupon->amount;
        //     // } elseif ($coupon->type === 'percent') {
        //     //     $couponReduction = ($coupon->amount / 100) * $total;
        //     // }
        //     $total -= $couponReduction;
        // }

        if (
            !$user->hasStoreCustomer(
                $store->id,
                $storeSettings->currency,
                $gateway
            )
        ) {
            $user->createStoreCustomer(
                $store->id,
                $storeSettings->currency,
                $gateway
            );
        }

        $customer = $user->getStoreCustomer(
            $store->id,
            $storeSettings->currency,
            $gateway
        );

        $storeCustomer = null;

        if (!$cashOrder) {
            $storeCustomer = $user->getStoreCustomer(
                $store->id,
                $storeSettings->currency,
                $gateway,
                true
            );
        }

        $total = $request->get('grandTotal');
        // $total += $salesTax;

        if (!$weeklyPlan) {
            if ($gateway === Constants::GATEWAY_STRIPE) {
                $storeSource = \Stripe\Source::create(
                    [
                        "customer" => $this->user->stripe_id,
                        "original_source" => $card->stripe_id,
                        "usage" => "single_use"
                    ],
                    ["stripe_account" => $storeSettings->stripe_id]
                );

                $charge = \Stripe\Charge::create(
                    [
                        "amount" => round($total * 100),
                        "currency" => $storeSettings->currency,
                        "source" => $storeSource,
                        "application_fee" => round(
                            $afterDiscountBeforeFees * $application_fee
                        )
                    ],
                    ["stripe_account" => $storeSettings->stripe_id]
                );
            } elseif ($gateway === Constants::GATEWAY_AUTHORIZE) {
                $billing = Billing::init($gateway, $store);

                $charge = new \App\Billing\Charge();
                $charge->amount = round($total * 100);
                $charge->customer = $customer;
                $charge->card = $card;

                $transactionId = $billing->charge($charge);
                $charge->id = $transactionId;
            }

            $balance = null;

            if ($cashOrder && !$store->modules->cashOrderNoBalance) {
                $balance = $total;
            }

            $order = new Order();
            $order->user_id = $user->id;
            $order->customer_id = $customer->id;
            $order->card_id = $cardId;
            $order->store_id = $store->id;
            $order->order_number = strtoupper(
                substr(uniqid(rand(10, 99), false), 0, 10)
            );
            $order->preFeePreDiscount = $preFeePreDiscount;
            $order->mealPlanDiscount = $mealPlanDiscount;
            $order->afterDiscountBeforeFees = $afterDiscountBeforeFees;
            $order->deliveryFee = $deliveryFee;
            $order->processingFee = $processingFee;
            $order->salesTax = $salesTax;
            $order->amount = $total;
            $order->balance = $balance;
            $order->currency = $storeSettings->currency;
            $order->fulfilled = false;
            $order->pickup = $request->get('pickup', 0);
            $order->delivery_date = date('Y-m-d', strtotime($deliveryDay));
            $order->paid = true;
            if (!$cashOrder) {
                $order->stripe_id = $charge->id;
            } else {
                $order->stripe_id = null;
            }
            $order->paid_at = new Carbon();
            $order->coupon_id = $couponId;
            $order->couponReduction = $couponReduction;
            $order->couponCode = $couponCode;
            $order->pickup_location_id = $pickupLocation;
            $order->transferTime = $transferTime;
            $order->cashOrder = $cashOrder;
            $order->payment_gateway = $gateway;
            if ($store->modules->dailyOrderNumbers) {
                $order->dailyOrderNumber = $dailyOrderNumber;
            }
            $order->originalAmount = $total * $deposit;
            $order->save();

            $order_transaction = new OrderTransaction();
            $order_transaction->order_id = $order->id;
            $order_transaction->store_id = $store->id;
            $order_transaction->user_id = $user->id;
            $order_transaction->customer_id = $customer->id;
            $order_transaction->type = 'order';
            if (!$cashOrder) {
                $order_transaction->stripe_id = $charge->id;
                $order_transaction->card_id = $cardId;
            } else {
                $order_transaction->stripe_id = null;
                $order_transaction->card_id = null;
            }
            $order_transaction->amount = $total * $deposit;
            $order_transaction->save();

            $items = $bag->getItems();
            foreach ($items as $item) {
                $mealOrder = new MealOrder();
                $mealOrder->order_id = $order->id;
                $mealOrder->store_id = $store->id;
                $mealOrder->meal_id = $item['meal']['id'];
                $mealOrder->quantity = $item['quantity'];
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
                        $mealPackageOrder->price = $item['package_price'];
                        $mealPackageOrder->save();

                        $mealOrder->meal_package_order_id =
                            $mealPackageOrder->id;
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

                $attachments = MealAttachment::where(
                    'meal_id',
                    $item['meal']['id']
                )->get();
                if ($attachments) {
                    foreach ($attachments as $attachment) {
                        $mealOrder = new MealOrder();
                        $mealOrder->order_id = $order->id;
                        $mealOrder->store_id = $store->id;
                        $mealOrder->meal_id = $attachment->attached_meal_id;
                        $mealOrder->quantity =
                            $attachment->quantity * $item['quantity'];
                        $mealOrder->attached = 1;
                        $mealOrder->save();
                    }
                }
            }

            // Send notification to store
            if ($storeSettings->notificationEnabled('new_order')) {
                $store->sendNotification('new_order', [
                    'order' => $order ?? null,
                    'pickup' => $pickup ?? null,
                    'card' => $card ?? null,
                    'customer' => $customer ?? null,
                    'subscription' => null
                ]);
            }

            // Send notification
            /*$email = new NewOrder([
                'order' => $order ?? null,
                'pickup' => $pickup ?? null,
                'card' => $card ?? null,
                'customer' => $customer ?? null,
                'subscription' => null
            ]);
            try {
                Mail::to($user)
                    ->bcc('mike@goprep.com')
                    ->send($email);
            } catch (\Exception $e) {
            }*/

            try {
                $user->sendNotification('new_order', [
                    'order' => $order ?? null,
                    'pickup' => $pickup ?? null,
                    'card' => $card ?? null,
                    'customer' => $customer ?? null,
                    'subscription' => null
                ]);
            } catch (\Exception $e) {
            }
        } else {
            $weekIndex = date('N', strtotime($deliveryDay));

            // Get cutoff date for selected delivery day
            $cutoff = $store->getCutoffDate(new Carbon($deliveryDay));

            // How long into the future is the delivery day? In days
            $diff = (strtotime($deliveryDay) - time()) / 86400;

            // Set billing anchor to now +2 mins
            $billingAnchor = Carbon::now()->addMinutes(2);

            // Selected start date is more than 1 week into the future.
            // Wait until next week to start billing cycle
            if ($diff >= 7) {
                $billingAnchor->addWeeks(1);
            }
            if (!$cashOrder) {
                if ($gateway === Constants::GATEWAY_STRIPE) {
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
                            "currency" => $storeSettings->currency
                        ],
                        ['stripe_account' => $storeSettings->stripe_id]
                    );

                    $token = \Stripe\Token::create(
                        [
                            "customer" => $this->user->stripe_id
                        ],
                        ['stripe_account' => $storeSettings->stripe_id]
                    );

                    $storeSource = $storeCustomer->sources->create(
                        [
                            'source' => $token
                        ],
                        ['stripe_account' => $storeSettings->stripe_id]
                    );

                    $subscription = $storeCustomer->subscriptions->create(
                        [
                            'default_source' => $storeSource,
                            'items' => [['plan' => $plan]],
                            'application_fee_percent' => $application_fee,
                            'trial_end' => $billingAnchor->getTimestamp()
                            //'prorate' => false
                        ],
                        ['stripe_account' => $storeSettings->stripe_id]
                    );
                } elseif ($gateway === Constants::GATEWAY_AUTHORIZE) {
                    $billing = Billing::init($gateway, $store);

                    $subscription = new \App\Billing\Subscription();
                    $subscription->amount = round($total * 100);
                    $subscription->customer = $customer;
                    $subscription->card = $card;
                    $subscription->startDate = $billingAnchor;
                    $subscription->period = Constants::PERIOD_WEEKLY;

                    $transactionId = $billing->subscribe($subscription);
                    $subscription->id = $transactionId;
                }

                $userSubscription = new Subscription();
                $userSubscription->user_id = $user->id;
                $userSubscription->customer_id = $customer->id;
                $userSubscription->stripe_customer_id = $storeCustomer->id;
                $userSubscription->store_id = $store->id;
                $userSubscription->name =
                    "Weekly subscription (" . $store->storeDetail->name . ")";
                if (!$cashOrder) {
                    $userSubscription->stripe_plan = $plan->id;
                    $userSubscription->stripe_id = substr($subscription->id, 4);
                } else {
                    $userSubscription->stripe_id =
                        'C -' .
                        strtoupper(substr(uniqid(rand(10, 99), false), 0, 10));
                    $userSubscription->stripe_plan = 'cash';
                }
                $userSubscription->quantity = 1;
                $userSubscription->preFeePreDiscount = $preFeePreDiscount;
                $userSubscription->mealPlanDiscount = $mealPlanDiscount;
                $userSubscription->afterDiscountBeforeFees = $afterDiscountBeforeFees;
                $userSubscription->processingFee = $processingFee;
                $userSubscription->deliveryFee = $deliveryFee;
                $userSubscription->salesTax = $salesTax;
                $userSubscription->amount = $total;
                $userSubscription->currency = $storeSettings->currency;
                $userSubscription->pickup = $request->get('pickup', 0);
                $userSubscription->interval = 'week';
                $userSubscription->delivery_day = date(
                    'N',
                    strtotime($deliveryDay)
                );
                $userSubscription->next_renewal_at = $cutoff
                    ->copy()
                    ->addDays(7);
                $userSubscription->coupon_id = $couponId;
                $userSubscription->couponReduction = $couponReduction;
                $userSubscription->couponCode = $couponCode;
                // In this case the 'next renewal time' is actually the first charge time
                $userSubscription->next_renewal_at = $billingAnchor->getTimestamp();
                $userSubscription->pickup_location_id = $pickupLocation;
                $userSubscription->transferTime = $transferTime;
                $userSubscription->cashOrder = $cashOrder;
                $userSubscription->save();

                // Create initial order
                $order = new Order();
                $order->user_id = $user->id;
                $order->customer_id = $customer->id;
                $order->card_id = $cardId;
                $order->store_id = $store->id;
                $order->subscription_id = $userSubscription->id;
                $order->order_number = strtoupper(
                    substr(uniqid(rand(10, 99), false), 0, 10)
                );
                $order->preFeePreDiscount = $preFeePreDiscount;
                $order->mealPlanDiscount = $mealPlanDiscount;
                $order->afterDiscountBeforeFees = $afterDiscountBeforeFees;
                $order->deliveryFee = $deliveryFee;
                $order->processingFee = $processingFee;
                $order->salesTax = $salesTax;
                $order->amount = $total;
                $order->currency = $storeSettings->currency;
                $order->fulfilled = false;
                $order->pickup = $request->get('pickup', 0);
                $order->delivery_date = (new Carbon(
                    $deliveryDay
                ))->toDateString();
                $order->coupon_id = $couponId;
                $order->couponReduction = $couponReduction;
                $order->couponCode = $couponCode;
                $order->pickup_location_id = $pickupLocation;
                $order->transferTime = $transferTime;
                if ($store->modules->dailyOrderNumbers) {
                    $order->dailyOrderNumber = $dailyOrderNumber;
                }
                $order->originalAmount = $total * $deposit;
                $order->cashOrder = $cashOrder;
                $order->save();

                foreach ($bag->getItems() as $item) {
                    $mealOrder = new MealOrder();
                    $mealOrder->order_id = $order->id;
                    $mealOrder->store_id = $store->id;
                    $mealOrder->meal_id = $item['meal']['id'];
                    $mealOrder->quantity = $item['quantity'];
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
                            $mealPackageOrder->quantity =
                                $item['package_quantity'];
                            $mealPackageOrder->price = $item['package_price'];
                            $mealPackageOrder->save();

                            $mealOrder->meal_package_order_id =
                                $mealPackageOrder->id;
                        } else {
                            $mealOrder->meal_package_order_id = MealPackageOrder::where(
                                [
                                    'meal_package_id' =>
                                        $item['meal_package_id'],
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

                    $attachments = MealAttachment::where(
                        'meal_id',
                        $item['meal']['id']
                    )->get();
                    if ($attachments) {
                        foreach ($attachments as $attachment) {
                            $mealOrder = new MealOrder();
                            $mealOrder->order_id = $order->id;
                            $mealOrder->store_id = $store->id;
                            $mealOrder->meal_id = $attachment->attached_meal_id;
                            $mealOrder->quantity =
                                $attachment->quantity * $item['quantity'];
                            $mealOrder->save();
                        }
                    }
                }

                foreach ($bag->getItems() as $item) {
                    $mealSub = new MealSubscription();
                    $mealSub->subscription_id = $userSubscription->id;
                    $mealSub->store_id = $store->id;
                    $mealSub->meal_id = $item['meal']['id'];
                    $mealSub->quantity = $item['quantity'];
                    if (isset($item['size']) && $item['size']) {
                        $mealSub->meal_size_id = $item['size']['id'];
                    }
                    if (isset($item['special_instructions'])) {
                        $mealSub->special_instructions =
                            $item['special_instructions'];
                    }
                    if ($item['meal_package'] === true) {
                        if (
                            MealPackageSubscription::where([
                                'meal_package_id' => $item['meal_package_id'],
                                'meal_package_size_id' =>
                                    $item['meal_package_size_id'],
                                'subscription_id' => $userSubscription->id
                            ])
                                ->get()
                                ->count() === 0
                        ) {
                            $mealPackageSubscription = new MealPackageSubscription();
                            $mealPackageSubscription->store_id = $store->id;
                            $mealPackageSubscription->subscription_id =
                                $userSubscription->id;
                            $mealPackageSubscription->meal_package_id =
                                $item['meal_package_id'];
                            $mealPackageSubscription->meal_package_size_id =
                                $item['meal_package_size_id'];
                            $mealPackageSubscription->quantity =
                                $item['package_quantity'];
                            $mealPackageSubscription->price =
                                $item['package_price'];
                            $mealPackageSubscription->save();

                            $mealSub->meal_package_subscription_id =
                                $mealPackageSubscription->id;
                        } else {
                            $mealSub->meal_package_subscription_id = MealPackageSubscription::where(
                                [
                                    'meal_package_id' =>
                                        $item['meal_package_id'],
                                    'meal_package_size_id' =>
                                        $item['meal_package_size_id'],
                                    'subscription_id' => $userSubscription->id
                                ]
                            )
                                ->pluck('id')
                                ->first();
                        }
                    }

                    $mealSub->save();

                    if (isset($item['components']) && $item['components']) {
                        foreach (
                            $item['components']
                            as $componentId => $choices
                        ) {
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

                    $attachments = MealAttachment::where(
                        'meal_id',
                        $item['meal']['id']
                    )->get();
                    if ($attachments) {
                        foreach ($attachments as $attachment) {
                            $mealSub = new MealSubscription();
                            $mealSub->subscription_id = $userSubscription->id;
                            $mealSub->store_id = $store->id;
                            $mealSub->meal_id = $attachment->attached_meal_id;
                            $mealSub->quantity =
                                $attachment->quantity * $item['quantity'];
                            $mealSub->save();
                        }
                    }
                }

                // Send notification to store
                if ($storeSettings->notificationEnabled('new_subscription')) {
                    $store->sendNotification('new_subscription', [
                        'order' => $order ?? null,
                        'pickup' => $pickup ?? null,
                        'card' => $card ?? null,
                        'customer' => $customer ?? null,
                        'subscription' => $userSubscription ?? null
                    ]);
                }

                // Send notification
                /*$email = new MealPlan([
                'order' => $order ?? null,
                'pickup' => $pickup ?? null,
                'card' => $card ?? null,
                'customer' => $customer ?? null,
                'subscription' => $userSubscription ?? null
            ]);

            try {
                Mail::to($user)
                    ->bcc('mike@goprep.com')
                    ->send($email);
            } catch (\Exception $e) {
            }*/

                try {
                    $user->sendNotification('meal_plan', [
                        'order' => $order ?? null,
                        'pickup' => $pickup ?? null,
                        'card' => $card ?? null,
                        'customer' => $customer ?? null,
                        'subscription' => $userSubscription ?? null
                    ]);
                } catch (\Exception $e) {
                }
            }
        }
    }
}
