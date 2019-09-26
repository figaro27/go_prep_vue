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
use App\Order;
use App\Store;
use App\StoreDetail;
use App\Subscription;
use App\Coupon;
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
        $store = Store::with(['settings', 'storeDetail'])->findOrFail($storeId);

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

        $cashOrder = $request->get('cashOrder');
        if ($cashOrder) {
            $cardId = null;
            $card = null;
        } else {
            $cardId = $request->get('card_id');
            $card = $this->user->cards()->findOrFail($cardId);
        }

        $application_fee = $store->settings->application_fee;
        $total = $request->get('subtotal');
        $subtotal = $request->get('subtotal');
        $preFeePreDiscount = $request->get('subtotal');
        $afterDiscountBeforeFees = $request->get('afterDiscount');

        $processingFee = 0;
        $mealPlanDiscount = 0;
        $salesTax = $request->get('salesTax');

        $today = Carbon::now()->toDateString();
        $count = DB::table('orders')
            ->where('store_id', $storeId)
            ->whereDate('created_at', $today)
            ->get()
            ->count();
        $dailyOrderNumber = $count + 1;

        if ($store->settings->applyMealPlanDiscount && $weeklyPlan) {
            $discount = $store->settings->mealPlanDiscount / 100;
            $mealPlanDiscount = $total * $discount;
            $total -= $mealPlanDiscount;
            $afterDiscountBeforeFees = $total;
        }

        if ($store->settings->applyDeliveryFee) {
            $total += $deliveryFee;
        }

        if ($store->settings->applyProcessingFee) {
            if ($store->settings->processingFeeType === 'flat') {
                $processingFee += $store->settings->processingFee;
            } elseif ($store->settings->processingFeeType === 'percent') {
                $processingFee +=
                    ($store->settings->processingFee / 100) * $subtotal;
            }

            $total += $processingFee;
        }

        if ($couponId != null) {
            // $coupon = Coupon::where('id', $couponId)->first();
            // $couponReduction = 0;
            // if ($coupon->type === 'flat') {
            //     $couponReduction = $coupon->amount;
            // } elseif ($coupon->type === 'percent') {
            //     $couponReduction = ($coupon->amount / 100) * $total;
            // }
            $total -= $couponReduction;
        }

        if (!$user->hasStoreCustomer($store->id)) {
            $storeCustomer = $user->createStoreCustomer($store->id);
        }

        $total += $salesTax;

        $storeCustomer = $user->getStoreCustomer($store->id);
        $customer = $user->getStoreCustomer($store->id, false);

        if (!$weeklyPlan) {
            if (!$cashOrder) {
                $storeSource = \Stripe\Source::create(
                    [
                        "customer" => $this->user->stripe_id,
                        "original_source" => $card->stripe_id,
                        "usage" => "single_use"
                    ],
                    ["stripe_account" => $store->settings->stripe_id]
                );

                $charge = \Stripe\Charge::create(
                    [
                        "amount" => round($total * 100),
                        "currency" => $store->settings->currency,
                        "source" => $storeSource,
                        "application_fee" => round(
                            $afterDiscountBeforeFees * $application_fee
                        )
                    ],
                    ["stripe_account" => $store->settings->stripe_id]
                );
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
            $order->currency = $store->settings->currency;
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
            $order->dailyOrderNumber = $dailyOrderNumber;
            $order->save();

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

            // Send notification to store
            if ($store->settings->notificationEnabled('new_order')) {
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
                        "currency" => $store->settings->currency
                    ],
                    ['stripe_account' => $store->settings->stripe_id]
                );

                $token = \Stripe\Token::create(
                    [
                        "customer" => $this->user->stripe_id
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
            $userSubscription->currency = $store->settings->currency;
            $userSubscription->pickup = $request->get('pickup', 0);
            $userSubscription->interval = 'week';
            $userSubscription->delivery_day = date(
                'N',
                strtotime($deliveryDay)
            );
            $userSubscription->next_renewal_at = $cutoff->copy()->addDays(7);
            $userSubscription->coupon_id = $couponId;
            $userSubscription->couponReduction = $couponReduction;
            $userSubscription->couponCode = $couponCode;
            // In this case the 'next renewal time' is actually the first charge time
            $userSubscription->next_renewal_at = $billingAnchor->getTimestamp();
            $userSubscription->pickup_location_id = $pickupLocation;
            $userSubscription->transferTime = $transferTime;
            $userSubscription->save();

            // Create initial order
            $order = new Order();
            $order->user_id = $user->id;
            $order->customer_id = $customer->id;
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
                $mealOrder->special_instructions =
                    $item['special_instructions'];
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

            foreach ($bag->getItems() as $item) {
                $mealSub = new MealSubscription();
                $mealSub->subscription_id = $userSubscription->id;
                $mealSub->store_id = $store->id;
                $mealSub->meal_id = $item['meal']['id'];
                $mealSub->quantity = $item['quantity'];
                if (isset($item['size']) && $item['size']) {
                    $mealSub->meal_size_id = $item['size']['id'];
                }
                $mealSub->special_instructions = $item['special_instructions'];
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

        /*
    $subscription = $user->newSubscription('main', $plan->id)->create($stripeToken['id']);
    $subscription->store_id = $this->store->id;
    $subscription->amount = $total;
    $subscription->interval = 'week';*/
    }
}
