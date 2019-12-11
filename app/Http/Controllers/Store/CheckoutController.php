<?php

namespace App\Http\Controllers\Store;

use App\Bag;
use App\Billing\Billing;
use App\User;
use App\Http\Controllers\Store\StoreController;
use App\Mail\Customer\MealPlan;
use App\Mail\Customer\NewOrder;
use App\Mail\Customer\NewGiftCard;
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
use App\Card;
use App\Customer;
use App\LineItem;
use App\LineItemOrder;
use App\MealPackageOrder;
use App\MealPackageSubscription;
use App\OrderBag;
use App\PurchasedGiftCard;
use App\Billing\Constants;
use App\Billing\Charge;
use App\Billing\Authorize;
use Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use DB;
use App\Traits\DeliveryDates;

class CheckoutController extends StoreController
{
    use DeliveryDates;

    public function orderBag($order_id)
    {
        $order_bags = OrderBag::where('order_id', $order_id)
            ->orderBy('id', 'asc')
            ->get();
        $data = [];

        if ($order_bags) {
            foreach ($order_bags as $order_bag) {
                $data[] = json_decode($order_bag->bag);
            }
        }

        return [
            'order_bags' => $data
        ];
    }

    public function checkout(\App\Http\Requests\CheckoutRequest $request)
    {
        $user = auth('api')->user();
        $storeId = $request->get('store_id');
        $store = Store::with([
            'settings',
            'modules',
            'storeDetail'
        ])->findOrFail($storeId);

        $store->setTimezone();
        $storeName = strtolower($store->storeDetail->name);

        $bagItems = $request->get('bag');
        $bag = new Bag($bagItems, $store);

        $bagTotal = $bag->getTotal() + $request->get('lineItemTotal');
        $weeklyPlan = $request->get('plan');
        $pickup = $request->get('pickup');
        $deliveryDay = $request->get('delivery_day');
        $isMultipleDelivery = (int) $request->get('isMultipleDelivery');
        $couponId = $request->get('coupon_id');
        $couponReduction = $request->get('couponReduction');
        $couponCode = $request->get('couponCode');
        $purchasedGiftCardId = $request->get('purchased_gift_card_id');
        $purchasedGiftCardReduction = $request->get(
            'purchasedGiftCardReduction'
        );
        $deliveryFee = $request->get('deliveryFee');
        $pickupLocation = $request->get('pickupLocation');
        $transferTime = $request->get('transferTime');
        $interval = $request->get('plan_interval', Constants::INTERVAL_WEEK);
        $period = Constants::PERIOD[$interval] ?? Constants::PERIOD_WEEKLY;
        $notes = $request->get('notes');
        $publicOrderNotes = $request->get('publicOrderNotes');
        //$stripeToken = $request->get('token');

        $application_fee = $store->settings->application_fee;

        $total = $request->get('subtotal');
        $subtotal = $request->get('subtotal');
        $preFeePreDiscount = $subtotal;
        $afterDiscountBeforeFees = $request->get('afterDiscount');
        $deposit = $request->get('deposit') ? $request->get('deposit') : 0;

        $processingFee = $request->get('processingFee');
        $mealPlanDiscount = $request->get('mealPlanDiscount');
        $salesTax = $request->get('salesTax');
        $customSalesTax =
            $request->get('customSalesTax') !== null
                ? $request->get('customSalesTax')
                : 0;

        $dailyOrderNumber = 0;
        if (!$isMultipleDelivery) {
            $max = Order::where('store_id', $storeId)
                ->whereDate('delivery_date', $deliveryDay)
                ->max('dailyOrderNumber');

            if ($max) {
                $dailyOrderNumber = $max + 1;
            } else {
                $dailyOrderNumber = 1;
            }
        }

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
        $userId = Customer::where('id', $customerId)
            ->pluck('user_id')
            ->first();
        $customerUser = User::where('id', $userId)->first();

        // $total += $salesTax;
        $total = $request->get('grandTotal');

        $cashOrder = $request->get('cashOrder');
        if ($cashOrder) {
            $cardId = null;
            $card = null;
            $gateway = Constants::GATEWAY_CASH;
        } else {
            $cardId = $request->get('card_id');
            $card = Card::where('id', $cardId)->first();
            $gateway = $card->payment_gateway;
        }

        $storeSettings = $this->store->settings;

        if (
            !$customerUser->hasStoreCustomer(
                $store->id,
                $storeSettings->currency,
                $gateway
            )
        ) {
            $customerUser->createStoreCustomer(
                $store->id,
                $storeSettings->currency,
                $gateway
            );
        }

        $customer = $customerUser->getStoreCustomer(
            $store->id,
            $storeSettings->currency,
            $gateway
        );

        $storeCustomer = null;

        if (!$cashOrder) {
            $storeCustomer = $customerUser->getStoreCustomer(
                $store->id,
                $storeSettings->currency,
                $gateway,
                true
            );
        }

        if (!$weeklyPlan) {
            if ($gateway === Constants::GATEWAY_STRIPE) {
                $storeSource = \Stripe\Source::create(
                    [
                        "customer" => $customerUser->stripe_id,
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

            $noBalance = $request->get('noBalance');

            if ($cashOrder && !$noBalance) {
                $balance = $total;
            }

            if ($deposit > 0 && !$noBalance) {
                $balance = $total - $deposit;
            }

            $order = new Order();
            $order->user_id = $customerUser->id;
            $order->customer_id = $customer->id;
            $order->card_id = $cardId;
            $order->store_id = $store->id;
            $order->order_number = strtoupper(
                substr(uniqid(rand(10, 99), false), 0, 8)
            );
            $order->notes = $notes;
            $order->publicNotes = $publicOrderNotes;
            $order->preFeePreDiscount = $preFeePreDiscount;
            $order->mealPlanDiscount = $mealPlanDiscount;
            $order->afterDiscountBeforeFees = $afterDiscountBeforeFees;
            $order->deliveryFee = $deliveryFee;
            $order->processingFee = $processingFee;
            $order->salesTax = $salesTax;
            $order->customSalesTax = $customSalesTax;
            $order->amount = $total;
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
            $order->purchased_gift_card_id = $purchasedGiftCardId;
            $order->purchasedGiftCardReduction = $purchasedGiftCardReduction;
            $order->pickup_location_id = $pickupLocation;
            $order->transferTime = $transferTime;
            $order->deposit = $deposit;
            $order->balance = $balance;
            $order->manual = 1;
            $order->cashOrder = $cashOrder;
            $order->payment_gateway = $gateway;
            $order->dailyOrderNumber = $dailyOrderNumber;
            $order->originalAmount = $deposit > 0 ? $deposit : $total;
            $order->isMultipleDelivery = $isMultipleDelivery;
            $order->save();

            $order_transaction = new OrderTransaction();
            $order_transaction->order_id = $order->id;
            $order_transaction->store_id = $store->id;
            $order_transaction->user_id = $customerUser->id;
            $order_transaction->customer_id = $customer->id;
            $order_transaction->type = 'order';
            if (!$cashOrder) {
                $order_transaction->stripe_id = $charge->id;
                $order_transaction->card_id = $cardId;
            } else {
                $order_transaction->stripe_id = null;
                $order_transaction->card_id = null;
            }
            $order_transaction->amount = $deposit > 0 ? $deposit : $total;
            $order_transaction->save();

            foreach ($bag->getItems() as $item) {
                if (
                    isset($item['meal']['gift_card']) &&
                    $item['meal']['gift_card']
                ) {
                    $quantity = $item['quantity'];

                    for ($i = 0; $i < $quantity; $i++) {
                        $purchasedGiftCard = new PurchasedGiftCard();
                        $purchasedGiftCard->store_id = $store->id;
                        $purchasedGiftCard->user_id = $customerUser->id;
                        $purchasedGiftCard->order_id = $order->id;
                        $purchasedGiftCard->code = strtoupper(
                            substr(uniqid(rand(10, 99), false), 0, 6)
                        );
                        $purchasedGiftCard->amount = $item['meal']['price'];
                        $purchasedGiftCard->balance = $item['meal']['price'];
                        $purchasedGiftCard->emailRecipient = isset(
                            $item['emailRecipient']
                        )
                            ? $item['emailRecipient']
                            : null;
                        $purchasedGiftCard->save();

                        if (isset($item['emailRecipient'])) {
                            $email = new NewGiftCard([
                                'purchasedGiftCard' => $purchasedGiftCard,
                                'order' => $order
                            ]);
                            try {
                                Mail::to($item['emailRecipient'])->send($email);
                            } catch (\Exception $e) {
                            }
                        }
                    }
                } else {
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
                            if (
                                isset($item['delivery_day']) &&
                                $item['delivery_day']
                            ) {
                                $mealPackageOrder->delivery_date = $this->getDeliveryDateMultipleDelivery(
                                    $item['delivery_day']['day'],
                                    $isMultipleDelivery
                                );
                            }
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
                        'meal_package_size_id' => isset(
                            $item['meal_package_size_id']
                        )
                            ? $item['meal_package_size_id']
                            : null
                    ])->get();

                    if (count($explicitAttachments) > 0) {
                        $attachments = $explicitAttachments;
                    }

                    $mealPackageAttachments = MealAttachment::where([
                        'meal_id' => 0,
                        'meal_package_id' => isset($item['meal_package_id'])
                            ? $item['meal_package_id']
                            : null,
                        'meal_package_size_id' => isset(
                            $item['meal_package_size_id']
                        )
                            ? $item['meal_package_size_id']
                            : null
                    ])->get();

                    foreach (
                        $mealPackageAttachments
                        as $mealPackageAttachment
                    ) {
                        $attachments->push($mealPackageAttachment);
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
                            if (
                                isset($item['delivery_day']) &&
                                $item['delivery_day']
                            ) {
                                $mealOrder->delivery_date = $this->getDeliveryDateMultipleDelivery(
                                    $item['delivery_day']['day'],
                                    $isMultipleDelivery
                                );
                            }
                            $mealOrder->save();
                        }
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

                    $lineItemOrder = new LineItemOrder();
                    $lineItemOrder->store_id = $store->id;
                    $lineItemOrder->line_item_id = $id;
                    $lineItemOrder->order_id = $order->id;
                    $lineItemOrder->quantity = $quantity;
                    $lineItemOrder->save();
                }
            }

            // Send notification to store

            // if ($store->settings->notificationEnabled('new_order')) {
            //     $store->sendNotification('new_order', [
            //         'order' => $order ?? null,
            //         'pickup' => $pickup ?? null,
            //         'card' => $card ?? null,
            //         'customer' => $customer ?? null,
            //         'subscription' => null
            //     ]);
            // }

            // Send notification
            /*$email = new NewOrder([
                'order' => $order ?? null,
                'pickup' => $pickup ?? null,
                'card' => $card ?? null,
                'customer' => $customer ?? null,
                'subscription' => null
            ]);
            try {
                Mail::to($customerUser)
                    ->bcc('mike@goprep.com')
                    ->send($email);
            } catch (\Exception $e) {
            }*/
            if (isset($purchasedGiftCardId)) {
                $purchasedGiftCard = PurchasedGiftCard::where(
                    'id',
                    $purchasedGiftCardId
                )->first();
                $purchasedGiftCard->balance -= $purchasedGiftCardReduction;
                $purchasedGiftCard->update();
            }

            if ($bagItems && count($bagItems) > 0) {
                foreach ($bagItems as $bagItem) {
                    $orderBag = new OrderBag();
                    $orderBag->order_id = (int) $order->id;
                    $orderBag->bag = json_encode($bagItem);
                    $orderBag->save();
                }
            }

            if ($request->get('emailCustomer')) {
                try {
                    $customerUser->sendNotification('new_order', [
                        'order' => $order ?? null,
                        'pickup' => $pickup ?? null,
                        'card' => $card ?? null,
                        'customer' => $customer ?? null,
                        'subscription' => null
                    ]);
                } catch (\Exception $e) {
                }
            }

            // Send a copy of all orders to GoPrep email
            $goPrepOrders = User::first();
            if ($request->get('emailCustomer') === false) {
                try {
                    $goPrepOrders->sendNotification('new_order', [
                        'order' => $order ?? null,
                        'pickup' => $pickup ?? null,
                        'card' => $card ?? null,
                        'customer' => $customer ?? null,
                        'subscription' => null
                    ]);
                } catch (\Exception $e) {
                }
            }
        } else {
            $weekIndex = date('N', strtotime($deliveryDay));

            if (
                $interval == Constants::INTERVAL_MONTH &&
                !$store->modules->monthlyPlans
            ) {
                throw new Exception(
                    'Cannot create monthly plan with this store'
                );
            }

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

            // Is billing anchor past the cutoff?
            // Set to the cutoff date
            if ($billingAnchor->greaterThan($cutoff)) {
                $billingAnchor = $cutoff->copy();
            }

            if (!$cashOrder) {
                $plan = \Stripe\Plan::create(
                    [
                        "amount" => round($total * 100),
                        "interval" => $interval,
                        "product" => [
                            "name" =>
                                ucwords($period) .
                                " subscription (" .
                                $store->storeDetail->name .
                                ")"
                        ],
                        "currency" => $store->settings->currency
                    ],
                    ['stripe_account' => $store->settings->stripe_id]
                );

                $token = \Stripe\Token::create(
                    [
                        "customer" => $customerUser->stripe_id
                    ],
                    ['stripe_account' => $store->settings->stripe_id]
                );

                $storeSource = $storeCustomer->sources->create(
                    [
                        'source' => $token
                    ],
                    ['stripe_account' => $store->settings->stripe_id]
                );

                $subscription = $storeCustomer->subscriptions->create(
                    [
                        'default_source' => $storeSource,
                        'items' => [['plan' => $plan]],
                        'application_fee_percent' => $application_fee,
                        'trial_end' => $billingAnchor->getTimestamp()
                        //'prorate' => false
                    ],
                    ['stripe_account' => $store->settings->stripe_id]
                );
            }

            $userSubscription = new Subscription();
            $userSubscription->user_id = $customerUser->id;
            $userSubscription->customer_id = $customer->id;
            $userSubscription->card_id = $cardId;
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
            $userSubscription->pickup = $request->get('pickup', 0);
            $userSubscription->interval = $interval;
            $userSubscription->delivery_day = date(
                'N',
                strtotime($deliveryDay)
            );
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
            $order->user_id = $customerUser->id;
            $order->customer_id = $customer->id;
            $order->card_id = $cardId;
            $order->store_id = $store->id;
            $order->subscription_id = $userSubscription->id;
            $order->order_number = strtoupper(
                substr(uniqid(rand(10, 99), false), 0, 8)
            );
            $order->notes = $notes;
            $order->publicNotes = $publicOrderNotes;
            $order->preFeePreDiscount = $preFeePreDiscount;
            $order->mealPlanDiscount = $mealPlanDiscount;
            $order->afterDiscountBeforeFees = $afterDiscountBeforeFees;
            $order->deliveryFee = $deliveryFee;
            $order->processingFee = $processingFee;
            $order->salesTax = $salesTax;
            $order->customSalesTax = $customSalesTax;
            $order->amount = $total;
            $order->currency = $store->settings->currency;
            $order->fulfilled = false;
            $order->pickup = $request->get('pickup', 0);
            $order->delivery_date = (new Carbon($deliveryDay))->toDateString();
            $order->coupon_id = $couponId;
            $order->couponReduction = $couponReduction;
            $order->couponCode = $couponCode;
            $order->pickup_location_id = $pickupLocation;
            $order->transferTime = $transferTime;
            $order->dailyOrderNumber = $dailyOrderNumber;
            $order->cashOrder = $cashOrder;
            $order->originalAmount = $deposit > 0 ? $deposit : $total;
            $order->isMultipleDelivery = $isMultipleDelivery;
            $order->save();

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
                        if (
                            isset($item['delivery_day']) &&
                            $item['delivery_day']
                        ) {
                            $mealPackageOrder->delivery_date = $this->getDeliveryDateMultipleDelivery(
                                $item['delivery_day']['day'],
                                $isMultipleDelivery
                            );
                        }
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
                    'meal_package_size_id' => isset(
                        $item['meal_package_size_id']
                    )
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
                        if (
                            isset($item['delivery_day']) &&
                            $item['delivery_day']
                        ) {
                            $mealOrder->delivery_date = $this->getDeliveryDateMultipleDelivery(
                                $item['delivery_day']['day'],
                                $isMultipleDelivery
                            );
                        }
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
                $mealSub->price = $item['price'] * $item['quantity'];
                if (isset($item['size']) && $item['size']) {
                    $mealSub->meal_size_id = $item['size']['id'];
                }
                if (isset($item['special_instructions'])) {
                    $mealSub->special_instructions =
                        $item['special_instructions'];
                }
                if (isset($item['free'])) {
                    $mealSub->free = $item['free'];
                }
                if ($item['meal_package']) {
                    $mealSub->meal_package = $item['meal_package'];
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
                                'meal_package_id' => $item['meal_package_id'],
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
                    'meal_package_size_id' => isset(
                        $item['meal_package_size_id']
                    )
                        ? $item['meal_package_size_id']
                        : null
                ])->get();

                if (count($explicitAttachments) > 0) {
                    $attachments = $explicitAttachments;
                }

                if ($attachments) {
                    foreach ($attachments as $attachment) {
                        $mealSub = new MealSubscription();
                        $mealSub->subscription_id = $userSubscription->id;
                        $mealSub->store_id = $store->id;
                        $mealSub->meal_id = $attachment->attached_meal_id;
                        $mealOrder->meal_size_id =
                            $attachment->attached_meal_size_id;
                        $mealSub->quantity =
                            $attachment->quantity * $item['quantity'];
                        $mealSub->save();
                    }
                }
            }

            // Send notification to store
            if ($store->settings->notificationEnabled('new_subscription')) {
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

            if ($bagItems && count($bagItems) > 0) {
                foreach ($bagItems as $bagItem) {
                    $orderBag = new OrderBag();
                    $orderBag->order_id = (int) $order->id;
                    $orderBag->bag = json_encode($bagItem);
                    $orderBag->save();
                }
            }

            try {
                $customerUser->sendNotification('meal_plan', [
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
