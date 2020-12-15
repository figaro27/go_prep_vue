<?php

namespace App\Http\Controllers\User;

use App\Bag;
use App\Http\Controllers\User\UserController;
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
use App\StoreSetting;
use App\Subscription;
use App\Coupon;
use App\MealPackageOrder;
use App\MealPackageSubscription;
use App\MealPackage;
use App\MealPackageSize;
use App\PurchasedGiftCard;
use App\ReferralSetting;
use App\Referral;
use App\User;
use App\Customer;
use App\Billing\Billing;
use App\Billing\Constants;
use App\Billing\Charge;
use App\Billing\Authorize;
use App\Http\Requests\CheckoutRequest;
use Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\OrderBag;
use DB;
use Exception;
use App\Traits\DeliveryDates;
use App\SmsSetting;
use App\Billing\Exceptions\BillingException;
use App\Error;

class CheckoutController extends UserController
{
    use DeliveryDates;

    public function getReservedStock($meal)
    {
        // Accounts for meal stock in upcoming renewals of subscriptions
        $mealSubs = MealSubscription::where('meal_id', $meal->id)->get();
        $quantity = 0;
        foreach ($mealSubs as $mealSub) {
            $sub = Subscription::where(
                'id',
                $mealSub->subscription_id
            )->first();
            if ($sub->status == 'active' && $sub->renewalCount > 0) {
                $quantity += $mealSub->quantity;
            }
        }
        return $quantity;
    }

    public function checkout(CheckoutRequest $request)
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
            $storeSettings = $store->settings;
            $bagItems = $request->get('bag');
            $bag = new Bag($bagItems, $store);
            $weeklyPlan = $request->get('plan');

            if ($storeSettings->open === false) {
                return response()->json(
                    [
                        'message' => 'Ordering is currently closed.'
                    ],
                    400
                );
            }

            if (
                $user->has_active_subscription &&
                !$store->settings->allowMultipleSubscriptions
            ) {
                return response()->json(
                    [
                        'message' =>
                            'You have an active subscription. Please go to the subscriptions page if you\'d like to change the meals for your next order which will be automatically placed for you.'
                    ],
                    400
                );
            }

            // Checking all meals are in stock before proceeding
            if ($this->store->modules->stockManagement) {
                foreach ($bag->getItems() as $item) {
                    $meal = Meal::where('id', $item['meal']['id'])->first();
                    if ($meal && $meal->stock !== null) {
                        $reservedStock = $this->getReservedStock($meal);
                        if ($meal->stock < $item['quantity'] + $reservedStock) {
                            $stockLeft = $meal->stock - $reservedStock;
                            if ($stockLeft < 0) {
                                $stockLeft = 0;
                            }
                            return response()->json(
                                [
                                    'message' =>
                                        $meal->title .
                                        ' currently has ' .
                                        $stockLeft .
                                        ' left in stock. Please adjust your order and checkout again.'
                                ],
                                400
                            );
                        }
                    }
                }
            }

            // Preventing checkout if the meal has been made inactive or deleted since the time it was added to the bag.

            // foreach ($bag->getItems() as $item) {
            //     if (isset($item['meal'])) {
            //         $meal = Meal::where('id', $item['meal']['id'])
            //             ->withTrashed()
            //             ->first();
            //         if (!$meal->active || $meal->deleted_at !== null) {
            //             return response()->json(
            //                 [
            //                     'message' =>
            //                         $meal->title .
            //                         ' has been removed from the menu by us. It is now removed from your bag. Please adjust your order and checkout again.',
            //                     'removeableMeal' => $meal
            //                 ],
            //                 400
            //             );
            //         }
            //     }
            // }

            $pickup = $request->get('pickup');
            $shipping = $request->get('shipping');
            $deliveryDay = $request->get('delivery_day');
            $weekIndex = (int) date('N', strtotime($deliveryDay));
            $isMultipleDelivery = (int) $request->get('isMultipleDelivery');
            $couponId = $request->get('coupon_id');
            $couponReduction = $request->get('couponReduction');
            $couponCode = $request->get('couponCode');
            $couponReferralUserId = Coupon::where('id', $couponId)
                ->pluck('referral_user_id')
                ->first();

            $purchasedGiftCardId = $request->get('purchased_gift_card_id');
            $purchasedGiftCardReduction = $request->get(
                'purchasedGiftCardReduction'
            )
                ? $request->get('purchasedGiftCardReduction')
                : 0;
            $promotionReduction = $request->get('promotionReduction');
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
            //$stripeToken = $request->get('token');
            $deposit = 1;
            $pointsReduction = $request->get('pointsReduction');

            $cashOrder = $request->get('cashOrder');
            if ($cashOrder || $request->get('grandTotal') === 0) {
                $cardId = null;
                $card = null;
                $gateway = Constants::GATEWAY_CASH;
            } else {
                $cardId = $request->get('card_id');
                $card = $this->user->cards()->findOrFail($cardId);
                $gateway = $card->payment_gateway;
            }

            $storeSettings = $this->store->settings;
            $storeModules = $this->store->modules;

            /** @var DeliveryDay $customDD */
            $customDD = null;

            // Delivery day settings overrides
            if ($storeModules->customDeliveryDays) {
                $customDD = $this->store
                    ->deliveryDays()
                    ->where([
                        'day' => $weekIndex,
                        'type' => $pickup ? 'pickup' : 'delivery'
                    ])
                    ->first();

                if ($customDD) {
                    $storeSettings->setDeliveryDayContext($customDD, $pickup);
                }
            }

            if ($storeModules->multipleDeliveryDays) {
                // Get the nearest upcoming delivery date
                $lowestDiff = null;
                $now = Carbon::now();
                $closestFutureDate = null;
                foreach ($bag->getItems() as $item) {
                    $date = new Carbon($item['delivery_day']['day_friendly']);
                    $diff = $date->diffInDays($now);
                    if ($lowestDiff === null || $diff < $lowestDiff) {
                        $lowestDiff = $diff;
                        $closestFutureDate = $date;
                        $customDD = $this->store
                            ->deliveryDays()
                            ->where([
                                'day' => (int) $item['delivery_day']['day'],
                                'type' => $pickup ? 'pickup' : 'delivery'
                            ])
                            ->first();
                    }
                }
                if (isset($closestFutureDate)) {
                    $deliveryDay = $closestFutureDate;
                }
            }

            $application_fee = $storeSettings->application_fee;

            $total = $request->get('subtotal');
            $subtotal = $request->get('subtotal');
            $preFeePreDiscount = $subtotal;
            $afterDiscountBeforeFees = $request->get('afterDiscount');

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

            $promotionPointsAmount = $request->get('promotionPointsAmount');

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

            $total = $request->get('grandTotal')
                ? $request->get('grandTotal')
                : 0;
            // $total += $salesTax;

            if (!$weeklyPlan) {
                if ($gateway === Constants::GATEWAY_STRIPE) {
                    if ($total > 0.5) {
                        $storeSource = \Stripe\Source::create(
                            [
                                "customer" => $this->user->stripe_id,
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

                    try {
                        $transactionId = $billing->charge($charge);
                    } catch (BillingException $e) {
                        return response()->json(
                            [
                                'error' => $e->getMessage()
                            ],
                            500
                        );
                    }
                    $charge->id = $transactionId;
                }

                $balance = null;

                $noBalance = $request->get('noBalance');

                if ($cashOrder && !$noBalance) {
                    $balance = $total;
                }

                $order = new Order();
                $order->user_id = $user->id;
                $order->customer_id = $customer->id;
                $order->card_id = $cardId;
                $order->store_id = $store->id;
                $order->order_number =
                    strtoupper(substr(uniqid(rand(10, 99), false), -4)) .
                    chr(rand(65, 90)) .
                    rand(10, 99);
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
                $order->balance = $balance;
                $order->currency = $storeSettings->currency;
                $order->fulfilled = false;
                $order->pickup = $request->get('pickup', 0);
                $order->shipping = $shipping;
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
                $order->cashOrder = $cashOrder;
                $order->payment_gateway = $gateway;
                $order->dailyOrderNumber = $dailyOrderNumber;
                $order->originalAmount = $total * $deposit;
                $order->isMultipleDelivery = $isMultipleDelivery;

                // Assign custom delivery day
                if ($customDD) {
                    $order->delivery_day_id = $customDD->id;
                }

                $order->save();

                $orderId = $order->id;

                if (
                    $gateway === Constants::GATEWAY_CASH &&
                    $application_fee > 0
                ) {
                    // Charge must be at least .5
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
                }

                $items = $bag->getItems();
                foreach ($items as $item) {
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
                            $purchasedGiftCard->user_id = $user->id;
                            $purchasedGiftCard->order_id = $order->id;
                            $purchasedGiftCard->code = strtoupper(
                                substr(uniqid(rand(10, 99), false), 0, 6) .
                                    chr(rand(65, 90)) .
                                    rand(10, 99)
                            );
                            $purchasedGiftCard->amount = isset(
                                $item['meal']['value']
                            )
                                ? $item['meal']['value']
                                : $item['meal']['price'];
                            $purchasedGiftCard->balance = isset(
                                $item['meal']['value']
                            )
                                ? $item['meal']['value']
                                : $item['meal']['price'];
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
                                if (
                                    $meal->stock === 0 ||
                                    $meal->stock <=
                                        $this->getReservedStock($meal)
                                ) {
                                    $meal->lastOutOfStock = date('Y-m-d H:i:s');
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
                                $mealPackageOrder->mappingId = isset(
                                    $item['mappingId']
                                )
                                    ? $item['mappingId']
                                    : null;

                                $mealPackageOrder->category_id = isset(
                                    $item['category_id']
                                )
                                    ? $item['category_id']
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

                        $hidden = Meal::where('id', $item['meal']['id'])
                            ->pluck('hidden')
                            ->first();
                        $mealOrder->hidden = $hidden;
                        $mealOrder->category_id = isset(
                            $item['meal']['category_id']
                        )
                            ? $item['meal']['category_id']
                            : null;

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

                $user->sendNotification('new_order', [
                    'order' => $order ?? null,
                    'pickup' => $pickup ?? null,
                    'card' => $card ?? null,
                    'customer' => $customer ?? null,
                    'subscription' => null
                ]);

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
                    !$store->settings->allowMonthlySubscriptions
                ) {
                    throw new Exception(
                        'Cannot create monthly plan with this store'
                    );
                }

                if ($interval === 'week' || $monthlyPrepay) {
                    $intervalCount = 1;
                } elseif ($interval === 'biweek') {
                    $intervalCount = 2;
                } elseif ($interval === 'month') {
                    $intervalCount = 4;
                }

                // Get cutoff date for selected delivery day
                $cutoff = $store->getCutoffDate(
                    new Carbon($deliveryDay),
                    $customDD
                );

                // How long into the future is the delivery day? In days
                $diff = (strtotime($deliveryDay) - time()) / 86400;

                $billingAnchor = Carbon::now('utc');

                // Set to the cutoff date if the following conditions are met
                if (
                    $diff >= 7 ||
                    $billingAnchor->greaterThan($cutoff) ||
                    $store->settings->subscriptionRenewalType == 'cutoff'
                ) {
                    $billingAnchor = $cutoff->copy();
                    if ($billingAnchor->isPast()) {
                        $billingAnchor->addWeeks(1);
                    }
                }

                if ($monthlyPrepay) {
                    $period = 'Monthly prepay';
                }

                $stripeCustomerId = $storeCustomer
                    ? $storeCustomer->id
                    : 'NULL';
                if ($cashOrder) {
                    $stripeCustomerId = 'CASH';
                }
                if ($total === 0) {
                    $stripeCustomerId = 'NO_CHARGE';
                }

                $userSubscription = new Subscription();
                $userSubscription->user_id = $user->id;
                $userSubscription->customer_id = $customer->id;
                $userSubscription->card_id = $cardId;
                $userSubscription->stripe_customer_id = $stripeCustomerId;
                $userSubscription->store_id = $store->id;
                $userSubscription->name =
                    ucwords($period) .
                    " subscription (" .
                    $store->storeDetail->name .
                    ")";
                $userSubscription->stripe_id = strtoupper(
                    substr(uniqid(rand(10, 99), false), 0, 10)
                );
                $userSubscription->stripe_plan = 'GOPREP';
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
                $userSubscription->currency = $storeSettings->currency;
                $userSubscription->pickup = $request->get('pickup', 0);
                $userSubscription->shipping = $shipping;
                $userSubscription->interval = 'week';
                $userSubscription->intervalCount = $intervalCount;
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
                $userSubscription->purchased_gift_card_id = $purchasedGiftCardId;
                $userSubscription->purchasedGiftCardReduction = $purchasedGiftCardReduction;
                $userSubscription->promotionReduction = $promotionReduction;
                $userSubscription->pointsReduction = $pointsReduction;
                // In this case the 'next renewal time' is actually the first charge time
                $userSubscription->next_renewal_at = $billingAnchor
                    ->addHours(5)
                    ->minute(0)
                    ->second(0)
                    ->getTimestamp();
                $userSubscription->pickup_location_id = $pickupLocation;
                $userSubscription->transferTime = $transferTime;
                $userSubscription->cashOrder = $cashOrder;
                $userSubscription->isMultipleDelivery = $isMultipleDelivery;
                $userSubscription->currency = $store->settings->currency;
                $userSubscription->save();

                // Create initial order
                $order = new Order();
                $order->user_id = $user->id;
                $order->customer_id = $customer->id;
                $order->card_id = $cardId;
                $order->store_id = $store->id;
                $order->subscription_id = $userSubscription->id;
                $order->order_number =
                    strtoupper(substr(uniqid(rand(10, 99), false), -4)) .
                    chr(rand(65, 90)) .
                    rand(10, 99);
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
                $order->currency = $storeSettings->currency;
                $order->fulfilled = false;
                $order->pickup = $request->get('pickup', 0);
                $order->shipping = $shipping;
                $order->delivery_date = (new Carbon(
                    $deliveryDay
                ))->toDateString();
                $order->coupon_id = $couponId;
                $order->couponReduction = $couponReduction;
                $order->couponCode = $couponCode;
                $order->promotionReduction = $promotionReduction;
                $order->pointsReduction = $pointsReduction;
                $order->applied_referral_id = $appliedReferralId;
                $order->referralReduction = $referralReduction;
                $order->purchased_gift_card_id = $purchasedGiftCardId;
                $order->purchasedGiftCardReduction = $purchasedGiftCardReduction;
                $order->pickup_location_id = $pickupLocation;
                $order->transferTime = $transferTime;
                $order->dailyOrderNumber = $dailyOrderNumber;
                $order->originalAmount = $total * $deposit;
                $order->cashOrder = $cashOrder;
                $order->isMultipleDelivery = $isMultipleDelivery;
                $order->save();

                $orderId = $order->id;

                foreach ($bag->getItems() as $item) {
                    if ($this->store->modules->stockManagement) {
                        $meal = Meal::where('id', $item['meal']['id'])->first();
                        if ($meal && $meal->stock !== null) {
                            $meal->stock -= $item['quantity'];
                            if (
                                $meal->stock === 0 ||
                                $meal->stock <= $this->getReservedStock($meal)
                            ) {
                                $meal->lastOutOfStock = date('Y-m-d H:i:s');
                                $meal->active = 0;
                                Subscription::syncStock($meal);
                            }
                        }
                        $meal->update();
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
                            $mealPackageOrder->mappingId = isset(
                                $item['mappingId']
                            )
                                ? $item['mappingId']
                                : null;
                            $mealPackageOrder->category_id = isset(
                                $item['category_id']
                            )
                                ? $item['category_id']
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

                    $mealOrder->category_id = isset(
                        $item['meal']['category_id']
                    )
                        ? $item['meal']['category_id']
                        : null;

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
                            $mealPackageSubscription->category_id = isset(
                                $item['category_id']
                            )
                                ? $item['category_id']
                                : null;
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
                    $mealSub->category_id = isset($item['meal']['category_id'])
                        ? $item['meal']['category_id']
                        : null;
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
                if ($storeSettings->notificationEnabled('new_subscription')) {
                    $store->sendNotification('new_subscription', [
                        'order' => $order ?? null,
                        'pickup' => $pickup ?? null,
                        'card' => $card ?? null,
                        'customer' => $customer ?? null,
                        'subscription' => $userSubscription ?? null
                    ]);
                }

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
                if (
                    $store->settings->subscriptionRenewalType !== 'cutoff' &&
                    $diff <= 7
                ) {
                    // Renew & create the first order right away.
                    $userSubscription->renew();
                }
            }

            // Referrals
            $referralSettings = ReferralSetting::where(
                'store_id',
                $storeId
            )->first();

            if ($referralSettings->enabled) {
                // If first order only setting enabled and the user has placed orders already, return
                $previousOrders = $user->orders
                    ->where('store_id', $store->id)
                    ->count();
                if (
                    $referralSettings->frequency === 'firstOrder' &&
                    $previousOrders > 1
                ) {
                    return;
                }

                $referralUrlCode = $request->get('referralUrl');

                $userWasReferred = false;
                $userReferralId = null;
                foreach ($user->orders as $order) {
                    if ($order->referral_id !== null) {
                        $userWasReferred = true;
                        $userReferralId = Referral::where(
                            'id',
                            $order->referral_id
                        )
                            ->pluck('user_id')
                            ->first();
                    }
                }
                // If a referral code exists in the URL or in a coupon OR the referral settings are set to all orders and the user was previously referred
                if (
                    $referralUrlCode ||
                    $couponReferralUserId ||
                    ($referralSettings->frequency === 'allOrders' &&
                        $userWasReferred)
                ) {
                    // If both URL code & referral coupon code exists, prioritize coupon code
                    if ($couponReferralUserId) {
                        $referralUserId = $couponReferralUserId;
                    } else {
                        $referralUserId = User::where(
                            'referralUrlCode',
                            $referralUrlCode
                        )
                            ->pluck('id')
                            ->first();
                    }

                    if (
                        $referralSettings->frequency === 'allOrders' &&
                        $userWasReferred &&
                        $userReferralId !== null
                    ) {
                        $referralUserId = $userReferralId;
                    }

                    $referralAmount = 0;
                    if ($referralSettings->type === 'flat') {
                        $referralAmount = $referralSettings->amount;
                    } else {
                        $referralAmount =
                            ($referralSettings->amount / 100) * $total;
                    }

                    $referral = Referral::where([
                        'user_id' => $referralUserId,
                        'store_id' => $storeId
                    ])->first();

                    if (!$referral) {
                        // Create new referral
                        $referral = new Referral();
                        $referral->store_id = $storeId;
                        $referral->user_id = $referralUserId;
                        $referral->ordersReferred = 1;
                        $referral->amountReferred = $total;
                        $referral->code =
                            'R' .
                            strtoupper(
                                substr(uniqid(rand(10, 99), false), -3)
                            ) .
                            chr(rand(65, 90)) .
                            rand(0, 9);
                        $referral->balance = $referralAmount;
                        $referral->save();
                    } else {
                        $referral->ordersReferred += 1;
                        $referral->amountReferred += $total;
                        $referral->balance += $referralAmount;
                        $referral->update();
                    }
                    $order = Order::where('id', $orderId)->first();
                    $order->referral_id = $referral->id;
                    $order->update();

                    $referralUser = User::where('id', $referralUserId)->first();
                    if ($user->notificationEnabled('new_referral')) {
                        $referralUser->sendNotification('new_referral', [
                            'order' => $order ?? null,
                            'pickup' => $pickup ?? null,
                            'customer' => $customer ?? null,
                            'referral' => $referral,
                            'referralAmount' => $referralAmount
                        ]);
                    }
                }
            }

            if (isset($purchasedGiftCardId)) {
                $purchasedGiftCard = PurchasedGiftCard::where(
                    'id',
                    $purchasedGiftCardId
                )->first();
                $purchasedGiftCard->balance -= $purchasedGiftCardReduction;
                if ($purchasedGiftCard->balance < 0) {
                    $purchasedGiftCard->balance = 0;
                }
                $purchasedGiftCard->update();
            }

            if ($referralReduction > 0) {
                $referral = Referral::where('id', $appliedReferralId)->first();
                $referral->balance -= $referralReduction;
                if ($referral->balance < 0) {
                    $referral->balance = 0;
                }
                $referral->update();
            }

            $smsSetting = SmsSetting::where('store_id', $storeId)->first();
            try {
                if ($smsSetting && $smsSetting->autoAddCustomers) {
                    $smsSetting->addNewCustomerToContacts($customer);
                }

                if ($smsSetting && $smsSetting->autoSendOrderConfirmation) {
                    $smsSetting->sendOrderConfirmationSMS($customer, $order);
                }
            } catch (\Exception $e) {
            }
        } catch (\Exception $e) {
            $error = new Error();
            $error->store_id = $store->id;
            $error->user_id = $user->id;
            $error->type = 'Checkout';
            $error->error = $e->getMessage();
            $error->save();

            return response()->json(
                [
                    'message' => $e->getMessage()
                ],
                400
            );
        }
    }
}
