<?php

namespace App\Http\Controllers\User;

use App\Bag;
use App\Http\Controllers\User\UserController;
use App\Mail\Customer\MealPlan;
use App\Mail\Customer\NewOrder;
use App\MealOrder;
use App\MealSubscription;
use App\Order;
use App\Store;
use App\StoreDetail;
use App\Subscription;
use Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

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
        //$stripeToken = $request->get('token');

        $cardId = $request->get('card_id');

        $card = $this->user->cards()->findOrFail($cardId);

        $application_fee = $store->settings->application_fee;
        $total = $bag->getTotal();
        $afterDiscountBeforeFees = $bag->getTotal();
        $preFeePreDiscount = $bag->getTotal();

        $deliveryFee = 0;
        $processingFee = 0;
        $mealPlanDiscount = 0;
        $salesTax = $request->get('salesTax');

        if ($store->settings->applyMealPlanDiscount && $weeklyPlan) {
            $discount = $store->settings->mealPlanDiscount / 100;
            $mealPlanDiscount = $total * $discount;
            $total -= $mealPlanDiscount;
            $afterDiscountBeforeFees = $total;
        }

        if ($store->settings->applyDeliveryFee) {
            $deliveryFee += $store->settings->deliveryFee;
            $total += $deliveryFee;
        }

        if ($store->settings->applyProcessingFee) {
            $processingFee += $store->settings->processingFee;
            $total += $processingFee;
        }

        if (!$user->hasStoreCustomer($store->id)) {
            $storeCustomer = $user->createStoreCustomer($store->id);
        }

        $total += $salesTax;

        $storeCustomer = $user->getStoreCustomer($store->id);
        $customer = $user->getStoreCustomer($store->id, false);

        if (!$weeklyPlan) {
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
                    "currency" => "usd",
                    "source" => $storeSource,
                    "application_fee" => round(
                        $afterDiscountBeforeFees * $application_fee
                    )
                ],
                ["stripe_account" => $store->settings->stripe_id]
            );

            $order = new Order();
            $order->user_id = $user->id;
            $order->customer_id = $customer->id;
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
            $order->fulfilled = false;
            $order->pickup = $request->get('pickup', 0);
            $order->delivery_date = date('Y-m-d', strtotime($deliveryDay));
            $order->paid = true;
            $order->stripe_id = $charge->id;
            $order->paid_at = new Carbon();
            $order->save();

            foreach ($bag->getItems() as $item) {
                $mealOrder = new MealOrder();
                $mealOrder->order_id = $order->id;
                $mealOrder->store_id = $store->id;
                $mealOrder->meal_id = $item['meal']['id'];
                $mealOrder->quantity = $item['quantity'];
                if ($item['size']) {
                    $mealOrder->meal_size_id = $item['size']['id'];
                }
                $mealOrder->save();
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
            $email = new NewOrder([
                'order' => $order ?? null,
                'pickup' => $pickup ?? null,
                'card' => $card ?? null,
                'customer' => $customer ?? null,
                'subscription' => null
            ]);
            Mail::to($user)
                ->bcc('mike@goprep.com')
                ->send($email);
        } else {
            $weekIndex = date('N', strtotime($deliveryDay));
            $cutoff = $store->getNextCutoffDate($weekIndex);

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
                    'billing_cycle_anchor' => $cutoff->getTimestamp(),
                    'prorate' => false
                ],
                ['stripe_account' => $store->settings->stripe_id]
            );

            $userSubscription = new Subscription();
            $userSubscription->user_id = $user->id;
            $userSubscription->customer_id = $customer->id;
            $userSubscription->stripe_customer_id = $storeCustomer->id;
            $userSubscription->store_id = $store->id;
            $userSubscription->name =
                "Weekly subscription (" . $store->storeDetail->name . ")";
            $userSubscription->stripe_id = substr($subscription->id, 4);
            $userSubscription->stripe_plan = $plan->id;
            $userSubscription->quantity = 1;
            $userSubscription->preFeePreDiscount = $preFeePreDiscount;
            $userSubscription->mealPlanDiscount = $mealPlanDiscount;
            $userSubscription->afterDiscountBeforeFees = $afterDiscountBeforeFees;
            $userSubscription->processingFee = $processingFee;
            $userSubscription->deliveryFee = $deliveryFee;
            $userSubscription->salesTax = $salesTax;
            $userSubscription->amount = $total;
            $userSubscription->pickup = $request->get('pickup', 0);
            $userSubscription->interval = 'week';
            $userSubscription->delivery_day = date(
                'N',
                strtotime($deliveryDay)
            );
            $userSubscription->next_renewal_at = $cutoff->addDays(7);
            // $userSubscription->charge_time = $cutoff->getTimestamp();
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
            $order->fulfilled = false;
            $order->pickup = $request->get('pickup', 0);
            $order->delivery_date = (new Carbon($deliveryDay))->toDateString();
            $order->save();

            foreach ($bag->getItems() as $item) {
                $mealOrder = new MealOrder();
                $mealOrder->order_id = $order->id;
                $mealOrder->store_id = $store->id;
                $mealOrder->meal_id = $item['meal']['id'];
                $mealOrder->quantity = $item['quantity'];
                if ($item['size']) {
                    $mealOrder->meal_size_id = $item['size']['id'];
                }
                $mealOrder->save();
            }

            foreach ($bag->getItems() as $item) {
                $mealSub = new MealSubscription();
                $mealSub->subscription_id = $userSubscription->id;
                $mealSub->store_id = $store->id;
                $mealSub->meal_id = $item['meal']['id'];
                $mealSub->quantity = $item['quantity'];
                if ($item['size']) {
                    $mealSub->meal_size_id = $item['size']['id'];
                }
                $mealSub->save();
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
            $email = new MealPlan([
                'order' => $order ?? null,
                'pickup' => $pickup ?? null,
                'card' => $card ?? null,
                'customer' => $customer ?? null,
                'subscription' => $userSubscription ?? null
            ]);
            Mail::to($user)
                ->bcc('mike@goprep.com')
                ->send($email);
        }

        /*
    $subscription = $user->newSubscription('main', $plan->id)->create($stripeToken['id']);
    $subscription->store_id = $this->store->id;
    $subscription->amount = $total;
    $subscription->interval = 'week';*/
    }
}
