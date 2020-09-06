<?php

namespace App\Http\Controllers\Store;

use App\Bag;
use App\Billing\Billing;
use App\User;
use App\Http\Controllers\Store\StoreController;
use App\Mail\Customer\MealPlan;
use App\Mail\Customer\NewOrder;
use App\Mail\Customer\NewGiftCard;
use App\Meal;
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
use App\MealPackage;
use App\MealPackageSize;
use App\OrderBag;
use App\SubscriptionBag;
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
use App\Referral;
use App\SmsSetting;

class CheckoutController extends StoreController
{
    use DeliveryDates;

    public function orderBag($order_id)
    {
        // $order_bags = OrderBag::where('order_id', $order_id)
        //     ->orderBy('id', 'asc')
        //     ->get();
        // $data = [];
        // if ($order_bags) {
        //     foreach ($order_bags as $order_bag) {
        //         $data[] = json_decode($order_bag->bag);
        //     }
        // }
        // return [
        //     'order_bags' => $data
        // ];
    }

    public function checkout(\App\Http\Requests\CheckoutRequest $request)
    {
        try {
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
            $weeklyPlan = $request->get('plan');

            // Checking all meals are in stock before proceeding
            if ($this->store->modules->stockManagement) {
                foreach ($bag->getItems() as $item) {
                    $meal = Meal::where('id', $item['meal']['id'])->first();
                    if ($meal && $meal->stock !== null) {
                        if ($meal->stock < $item['quantity']) {
                            return response()->json(
                                [
                                    'message' =>
                                        $meal->title .
                                        ' currently has ' .
                                        $meal->stock .
                                        ' left in stock. Please adjust your order and checkout again.'
                                ],
                                400
                            );
                        }
                    }
                }
            }

            $bagTotal = $bag->getTotal() + $request->get('lineItemTotal');
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
            $promotionReduction = $request->get('promotionReduction');
            $pointsReduction = $request->get('pointsReduction');
            $appliedReferralId = $request->get('applied_referral_id');
            $referralReduction = $request->get('referralReduction');
            $deliveryFee = $request->get('deliveryFee');
            $gratuity = $request->get('gratuity');
            $coolerDeposit = $request->get('coolerDeposit');
            $pickupLocation = $request->get('pickupLocation');
            $transferTime = $request->get('transferTime');
            $monthlyPrepay = $request->get('monthlyPrepay');
            $interval = $request->get(
                'plan_interval',
                Constants::INTERVAL_WEEK
            );
            $period = Constants::PERIOD[$interval] ?? Constants::PERIOD_WEEKLY;
            $notes = $request->get('notes');
            $publicOrderNotes = $request->get('publicOrderNotes');
            $hot = $request->get('hot');
            $staff = $request->get('staff');
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

            $customerId = $request->get('customer');
            $userId = Customer::where('id', $customerId)
                ->pluck('user_id')
                ->first();
            $customerUser = User::where('id', $userId)->first();

            // $total += $salesTax;
            $total = $request->get('grandTotal')
                ? $request->get('grandTotal')
                : 0;

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

            $promotionPointsAmount = $request->get('promotionPointsAmount');

            if (!$cashOrder) {
                $storeCustomer = $customerUser->getStoreCustomer(
                    $store->id,
                    $storeSettings->currency,
                    $gateway,
                    true
                );
            }

            if (!$weeklyPlan) {
                $balance = null;

                $noBalance = $request->get('noBalance');

                if ($cashOrder && !$noBalance) {
                    $balance = $total;
                }

                if ($deposit > 0 && !$noBalance) {
                    $balance = $total - $deposit;
                }

                $total = $total - $balance;

                if ($gateway === Constants::GATEWAY_STRIPE) {
                    if ($total > 0.5) {
                        $storeSource = \Stripe\Source::create(
                            [
                                "customer" => $customerUser->stripe_id,
                                "original_source" => $card->stripe_id,
                                "usage" => "single_use"
                            ],
                            ["stripe_account" => $storeSettings->stripe_id]
                        );

                        try {
                            $charge = \Stripe\Charge::create(
                                [
                                    "amount" => round($total * 100),
                                    "currency" => $storeSettings->currency,
                                    "source" => $storeSource,
                                    "application_fee" => round(
                                        $afterDiscountBeforeFees *
                                            $application_fee
                                    )
                                ],
                                ["stripe_account" => $storeSettings->stripe_id],
                                [
                                    "idempotency_key" =>
                                        substr(
                                            uniqid(rand(10, 99), false),
                                            0,
                                            14
                                        ) .
                                        chr(rand(65, 90)) .
                                        rand(0, 9)
                                ]
                            );
                        } catch (\Stripe\Error\Charge $e) {
                            return response()->json(
                                [
                                    'error' => trim(
                                        json_encode(
                                            $e->jsonBody['error']['message']
                                        ),
                                        '"'
                                    )
                                ],
                                400
                            );
                        }
                    }
                } elseif ($gateway === Constants::GATEWAY_AUTHORIZE) {
                    $billing = Billing::init($gateway, $store);

                    $charge = new \App\Billing\Charge();
                    $charge->amount = round($total * 100);
                    $charge->customer = $customer;
                    $charge->card = $card;

                    $transactionId = $billing->charge($charge);
                    $charge->id = $transactionId;
                }

                $total = $request->get('grandTotal');

                $order = new Order();
                $order->user_id = $customerUser->id;
                $order->customer_id = $customer->id;
                $order->card_id = $cardId;
                $order->store_id = $store->id;
                $order->order_number =
                    strtoupper(substr(uniqid(rand(10, 99), false), -4)) .
                    chr(rand(65, 90)) .
                    rand(10, 99);
                $order->notes = $notes;
                $order->publicNotes = $publicOrderNotes;
                $order->preFeePreDiscount = $preFeePreDiscount;
                $order->mealPlanDiscount = $mealPlanDiscount;
                $order->afterDiscountBeforeFees = $afterDiscountBeforeFees;
                $order->deliveryFee = $deliveryFee;
                $order->gratuity = $gratuity;
                $order->coolerDeposit = $coolerDeposit;
                $order->processingFee = $processingFee;
                $order->salesTax = $salesTax;
                $order->customSalesTax = $customSalesTax;
                $order->amount = $total;
                $order->currency = $store->settings->currency;
                $order->fulfilled = false;
                $order->pickup = $request->get('pickup', 0);
                $order->delivery_date = date('Y-m-d', strtotime($deliveryDay));
                $order->paid = true;
                if (!$cashOrder && $total > 0.5) {
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
                $order->promotionReduction = $promotionReduction;
                $order->pointsReduction = $pointsReduction;
                $order->applied_referral_id = $appliedReferralId;
                $order->referralReduction = $referralReduction;
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
                $order->hot = $hot;
                $order->staff_id = $staff;
                $order->save();

                $orderId = $order->id;

                if ($gateway === Constants::GATEWAY_CASH) {
                    // Charge must be at least .50
                    if (
                        round($afterDiscountBeforeFees * $application_fee) /
                            100 >=
                        0.5
                    ) {
                        $charge = \Stripe\Charge::create([
                            'amount' => round(
                                $afterDiscountBeforeFees * $application_fee
                            ),
                            'currency' => $storeSettings->currency,
                            'source' => $storeSettings->stripe_id,
                            'description' =>
                                $store->storeDetail->name .
                                ' application fee for cash order #' .
                                $orderId .
                                ' - ' .
                                $order->order_number
                        ]);
                    }
                }

                if ($total > 0.5) {
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
                    $order_transaction->amount =
                        $deposit > 0 ? $deposit : $total;
                    $order_transaction->save();
                }

                foreach ($bag->getItems() as $item) {
                    if (
                        isset($item['meal']['gift_card']) &&
                        $item['meal']['gift_card']
                    ) {
                        $quantity = $item['quantity'];

                        for ($i = 0; $i < $quantity; $i++) {
                            $purchasedGiftCard = new PurchasedGiftCard();
                            $purchasedGiftCard->store_id = $store->id;
                            $purchasedGiftCard->gift_card_id =
                                $item['meal']['id'];
                            $purchasedGiftCard->user_id = $customerUser->id;
                            $purchasedGiftCard->order_id = $order->id;
                            $purchasedGiftCard->code = strtoupper(
                                substr(uniqid(rand(10, 99), false), 0, 6)
                            );
                            $purchasedGiftCard->amount = $item['meal']['price'];
                            $purchasedGiftCard->balance =
                                $item['meal']['price'];
                            $purchasedGiftCard->emailRecipient = isset(
                                $item['emailRecipient']
                            )
                                ? $item['emailRecipient']
                                : null;
                            $purchasedGiftCard->save();

                            if (isset($item['emailRecipient'])) {
                                $store->sendNotification('new_gift_card', [
                                    'order' => $order ?? null,
                                    'purchasedGiftCard' => $purchasedGiftCard,
                                    'emailRecipient' => $item['emailRecipient']
                                ]);
                            }
                        }
                    } else {
                        if ($this->store->modules->stockManagement) {
                            $meal = Meal::where(
                                'id',
                                $item['meal']['id']
                            )->first();
                            if ($meal && $meal->stock !== null) {
                                $meal->stock -= $item['quantity'];
                                if ($meal->stock === 0) {
                                    $meal->active = 0;
                                    Subscription::syncStock($meal);
                                }
                                $meal->update();
                            }
                        }

                        $mealOrder = new MealOrder();
                        $mealOrder->order_id = $order->id;
                        $mealOrder->store_id = $store->id;
                        $mealOrder->meal_id = $item['meal']['id'];
                        $mealOrder->quantity = $item['quantity'];
                        $mealOrder->price =
                            isset($item['top_level']) &&
                            $item['top_level'] == true
                                ? 0
                                : $item['price'] * $item['quantity'];

                        if (
                            isset($item['meal_package_variation']) &&
                            $item['meal_package_variation']
                        ) {
                            $mealOrder->meal_package_variation = 1;
                        }

                        if (!$item['meal_package']) {
                            $mealOrder->customTitle = isset(
                                $item['customTitle']
                            )
                                ? $item['customTitle']
                                : null;
                            $mealOrder->customSize = isset($item['customSize'])
                                ? $item['customSize']
                                : null;
                        }

                        if (
                            isset($item['delivery_day']) &&
                            $item['delivery_day']
                        ) {
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

                        if ($item['meal_package'] === true) {
                            if (
                                MealPackageOrder::where([
                                    'meal_package_id' =>
                                        $item['meal_package_id'],
                                    'meal_package_size_id' =>
                                        $item['meal_package_size_id'],
                                    'customTitle' => $item['customTitle'],
                                    'order_id' => $order->id,
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
                                $mealPackageOrder->customTitle = isset(
                                    $item['customTitle']
                                )
                                    ? $item['customTitle']
                                    : null;
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
                                $mealPackageOrder->price =
                                    $item['package_price'];
                                if (
                                    isset($item['delivery_day']) &&
                                    $item['delivery_day']
                                ) {
                                    $mealPackageOrder->delivery_date =
                                        $item['delivery_day']['day_friendly'];
                                }

                                $mealPackageOrder->customTitle = isset(
                                    $item['customTitle']
                                )
                                    ? $item['customTitle']
                                    : null;
                                $mealPackageOrder->customSize = isset(
                                    $item['customSize']
                                )
                                    ? $item['customSize']
                                    : null;
                                $mealPackageOrder->mappingId = isset(
                                    $item['mappingId']
                                )
                                    ? $item['mappingId']
                                    : null;
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
                                        'order_id' => $order->id,
                                        'customTitle' => $item['customTitle'],
                                        'order_id' => $order->id,
                                        'mappingId' => isset($item['mappingId'])
                                            ? $item['mappingId']
                                            : null
                                    ]
                                )
                                    ->pluck('id')
                                    ->first();
                            }
                        }

                        $hidden = Meal::where('id', $item['meal']['id'])
                            ->pluck('hidden')
                            ->first();
                        $mealOrder->hidden = $hidden;

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
                            'applyToAll' => 1
                        ])->get();

                        $explicitAttachments = MealAttachment::where([
                            'applyToAll' => 0,
                            'meal_id' => $item['meal']['id'],
                            'meal_size_id' => isset($item['size']['id'])
                                ? $item['size']['id']
                                : null
                        ])->get();

                        foreach ($explicitAttachments as $explicitAttachment) {
                            $attachments->push($explicitAttachment);
                        }

                        if ($attachments) {
                            foreach ($attachments as $attachment) {
                                $mealOrder = new MealOrder();
                                $mealOrder->order_id = $order->id;
                                $mealOrder->store_id = $store->id;
                                $mealOrder->meal_id =
                                    $attachment->attached_meal_id;
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
                                    $mealOrder->delivery_date =
                                        $item['delivery_day']['day_friendly'];
                                }
                                $mealOrder->save();
                            }
                        }
                    }
                }

                $lineItemsOrder = $request->get('lineItemsOrder');
                if ($lineItemsOrder != null) {
                    foreach ($lineItemsOrder as $lineItemOrder) {
                        $id = $lineItemOrder['id'];
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

                // if ($bagItems && count($bagItems) > 0) {
                //     foreach ($bagItems as $bagItem) {
                //         $orderBag = new OrderBag();
                //         $orderBag->order_id = (int) $order->id;
                //         $orderBag->bag = json_encode($bagItem);
                //         $orderBag->save();
                //     }
                // }

                // Delete the credit card if the user unchecked save for future use
                if ($card && !$card->saveCard) {
                    $card->delete();
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

                // Promotion Points
                if ($pointsReduction > 0) {
                    $customer->points -= $pointsReduction * 100;
                    $customer->update();
                }

                if ($promotionPointsAmount) {
                    $customer->points += $promotionPointsAmount;
                    $customer->update();
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

                // if ($billingAnchor->greaterThan($cutoff)) {
                //     $billingAnchor = $cutoff->copy();
                // }

                if ($store->settings->subscriptionRenewalType == 'cutoff') {
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
                $userSubscription->gratuity = $gratuity;
                $userSubscription->coolerDeposit = $coolerDeposit;
                $userSubscription->salesTax = $salesTax;
                $userSubscription->amount = $total;
                $userSubscription->pickup = $request->get('pickup', 0);
                $userSubscription->interval = $interval;
                $userSubscription->monthlyPrepay = $monthlyPrepay;
                $userSubscription->delivery_day = date(
                    'N',
                    strtotime($deliveryDay)
                );
                $userSubscription->coupon_id = $couponId;
                $userSubscription->couponReduction = $couponReduction;
                $userSubscription->couponCode = $couponCode;
                $userSubscription->applied_referral_id = $appliedReferralId;
                $userSubscription->referralReduction = $referralReduction;
                $userSubscription->promotionReduction = $promotionReduction;
                $userSubscription->pointsReduction = $pointsReduction;
                // In this case the 'next renewal time' is actually the first charge time
                $userSubscription->next_renewal_at = $billingAnchor->getTimestamp();
                $userSubscription->pickup_location_id = $pickupLocation;
                $userSubscription->transferTime = $transferTime;
                $userSubscription->cashOrder = $cashOrder;
                $userSubscription->isMultipleDelivery = $isMultipleDelivery;
                $userSubscription->save();

                // Create initial order
                $order = new Order();
                $order->user_id = $customerUser->id;
                $order->customer_id = $customer->id;
                $order->card_id = $cardId;
                $order->store_id = $store->id;
                $order->subscription_id = $userSubscription->id;
                $order->order_number =
                    strtoupper(substr(uniqid(rand(10, 99), false), -4)) .
                    chr(rand(65, 90)) .
                    rand(10, 99);
                $order->notes = $notes;
                $order->publicNotes = $publicOrderNotes;
                $order->preFeePreDiscount = $preFeePreDiscount;
                $order->mealPlanDiscount = $mealPlanDiscount;
                $order->afterDiscountBeforeFees = $afterDiscountBeforeFees;
                $order->deliveryFee = $deliveryFee;
                $order->gratuity = $gratuity;
                $order->coolerDeposit = $coolerDeposit;
                $order->processingFee = $processingFee;
                $order->salesTax = $salesTax;
                $order->customSalesTax = $customSalesTax;
                $order->amount = $total;
                $order->currency = $store->settings->currency;
                $order->fulfilled = false;
                $order->pickup = $request->get('pickup', 0);
                $order->delivery_date = (new Carbon(
                    $deliveryDay
                ))->toDateString();
                $order->coupon_id = $couponId;
                $order->couponReduction = $couponReduction;
                $order->applied_referral_id = $appliedReferralId;
                $order->referralReduction = $referralReduction;
                $order->promotionReduction = $promotionReduction;
                $order->pointsReduction = $pointsReduction;
                $order->couponCode = $couponCode;
                $order->pickup_location_id = $pickupLocation;
                $order->transferTime = $transferTime;
                $order->dailyOrderNumber = $dailyOrderNumber;
                $order->cashOrder = $cashOrder;
                $order->originalAmount = $deposit > 0 ? $deposit : $total;
                $order->isMultipleDelivery = $isMultipleDelivery;
                $order->hot = $hot;
                $order->staff_id = $staff;
                $order->save();

                $orderId = $order->id;

                foreach ($bag->getItems() as $item) {
                    if ($this->store->modules->stockManagement) {
                        $meal = Meal::where('id', $item['meal']['id'])->first();
                        if ($meal && $meal->stock !== null) {
                            $meal->stock -= $item['quantity'];
                            if ($meal->stock === 0) {
                                $meal->active = 0;
                                Subscription::syncStock($meal);
                            }
                            $meal->update();
                        }
                    }

                    $mealOrder = new MealOrder();
                    $mealOrder->order_id = $order->id;
                    $mealOrder->store_id = $store->id;
                    $mealOrder->meal_id = $item['meal']['id'];
                    $mealOrder->quantity = $item['quantity'];
                    $mealOrder->price =
                        isset($item['top_level']) && $item['top_level'] == true
                            ? 0
                            : $item['price'] * $item['quantity'];
                    if ($item['meal_package'] === false) {
                        $mealOrder->customTitle = isset($item['customTitle'])
                            ? $item['customTitle']
                            : null;
                        $mealOrder->customSize = isset($item['customSize'])
                            ? $item['customSize']
                            : null;
                    }

                    if (
                        isset($item['meal_package_variation']) &&
                        $item['meal_package_variation']
                    ) {
                        $mealOrder->meal_package_variation = 1;
                    }

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

                            $mealPackageOrder->customTitle = isset(
                                $item['customTitle']
                            )
                                ? $item['customTitle']
                                : null;
                            $mealPackageOrder->customSize = isset(
                                $item['customSize']
                            )
                                ? $item['customSize']
                                : null;
                            $mealPackageOrder->mappingId = isset(
                                $item['mappingId']
                            )
                                ? $item['mappingId']
                                : null;
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
                                    'order_id' => $order->id,
                                    'mappingId' => isset($item['mappingId'])
                                        ? $item['mappingId']
                                        : null
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
                        'applyToAll' => 1
                    ])->get();

                    $explicitAttachments = MealAttachment::where([
                        'applyToAll' => 0,
                        'meal_id' => $item['meal']['id'],
                        'meal_size_id' => isset($item['size']['id'])
                            ? $item['size']['id']
                            : null
                    ])->get();

                    foreach ($explicitAttachments as $explicitAttachment) {
                        $attachments->push($explicitAttachment);
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
                                $mealOrder->delivery_date =
                                    $item['delivery_day']['day_friendly'];
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
                    $mealSub->price =
                        isset($item['top_level']) && $item['top_level'] == true
                            ? 0
                            : $item['price'] * $item['quantity'];
                    if (isset($item['free'])) {
                        $mealSub->free = $item['free'];
                    }
                    if (isset($item['delivery_day']) && $item['delivery_day']) {
                        $mealSub->delivery_date =
                            $item['delivery_day']['day_friendly'];
                    }
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
                                'subscription_id' => $userSubscription->id,
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
                            if (
                                isset($item['delivery_day']) &&
                                $item['delivery_day']
                            ) {
                                $mealPackageSubscription->delivery_date =
                                    $item['delivery_day']['day_friendly'];
                            }
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
                                    'subscription_id' => $userSubscription->id,
                                    'mappingId' => isset($item['mappingId'])
                                        ? $item['mappingId']
                                        : null
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

                    $attachments = MealAttachment::where([
                        'meal_id' => $item['meal']['id'],
                        'applyToAll' => 1
                    ])->get();

                    $explicitAttachments = MealAttachment::where([
                        'applyToAll' => 0,
                        'meal_id' => $item['meal']['id'],
                        'meal_size_id' => isset($item['size']['id'])
                            ? $item['size']['id']
                            : null
                    ])->get();

                    foreach ($explicitAttachments as $explicitAttachment) {
                        $attachments->push($explicitAttachment);
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
                            if (
                                isset($item['delivery_day']) &&
                                $item['delivery_day']
                            ) {
                                $mealSub->delivery_date =
                                    $item['delivery_day']['day_friendly'];
                            }
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

                // if ($bagItems && count($bagItems) > 0) {
                //     foreach ($bagItems as $bagItem) {
                //         $orderBag = new OrderBag();
                //         $orderBag->order_id = (int) $order->id;
                //         $orderBag->bag = json_encode($bagItem);
                //         $orderBag->save();
                //     }
                // }

                // if ($bagItems && count($bagItems) > 0) {
                //     foreach ($bagItems as $bagItem) {
                //         $subscriptionBag = new SubscriptionBag();
                //         $subscriptionBag->subscription_id =
                //             (int) $userSubscription->id;
                //         $subscriptionBag->bag = json_encode($bagItem);
                //         $subscriptionBag->save();
                //     }
                // }

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

            if ($referralReduction > 0) {
                $referral = Referral::where('id', $appliedReferralId)->first();
                $referral->balance -= $referralReduction;
                $referral->update();
            }

            // Auto add new customer to SMS Contacts
            $smsSetting = SmsSetting::where('store_id', $storeId)->first();
            if ($smsSetting && $smsSetting->autoAddCustomers) {
                $smsSetting->addNewCustomerToContacts($customer);
            }

            if ($smsSetting && $smsSetting->autoSendOrderConfirmation) {
                $smsSetting->sendOrderConfirmationSMS($customer, $order);
            }

            return $orderId;
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => $e->getMessage()
                ],
                400
            );
        }
    }
}
