<?php

namespace App\Http\Controllers\User;

use App\Bag;
use App\Http\Controllers\User\UserController;
use App\Mail\Customer\NewOrder;
use App\MealOrder;
use App\Order;
use App\Store;
use App\StoreDetail;
use App\Subscription;
use Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class CheckoutController extends UserController
{
    public function checkout(\App\Http\Requests\CheckoutRequest $request)
    {
        $user = auth('api')->user();

        $bag = new Bag($request->get('bag'));
        $weeklyPlan = $request->get('plan');
        $pickup = $request->get('pickup');
        $deliveryDay = $request->get('delivery_day');
        //$stripeToken = $request->get('token');
        $storeId = $request->get('store_id');
        $cardId = $request->get('card_id');

        $store = Store::with(['settings', 'storeDetail'])->findOrFail($storeId);
        $card = $this->user->cards()->findOrFail($cardId);

        $application_fee = $store->settings->application_fee;
        $total = $bag->getTotal();
        $preFeeTotal = $bag->getTotal();

        $deliveryFee = 0;
        $processingFee = 0;
        $mealPlanDiscount = 0;

        if ($store->settings->applyDeliveryFee) {
            $total += $store->settings->deliveryFee;
            $deliveryFee += $store->settings->deliveryFee;
        }

        if ($store->settings->applyProcessingFee) {
            $total += $store->settings->processingFee;
            $processingFee += $store->settings->processingFee;
        }

        if ($store->settings->applyMealPlanDiscount) {
            $discount = $store->settings->mealPlanDiscount / 100;
            $total -= ($preFeeTotal * $discount);
            $preFeeTotal -= ($preFeeTotal * $discount);
            $mealPlanDiscount = (($store->settings->mealPlanDiscount / 100) * $preFeeTotal);
        }

        if (!$user->hasStoreCustomer($store->id)) {
            $storeCustomer = $user->createStoreCustomer($store->id);
        }
        $storeCustomer = $user->getStoreCustomer($store->id);
        $customer = $user->getStoreCustomer($store->id, false);

        if (!$weeklyPlan) {
            $storeSource = \Stripe\Source::create([
                "customer" => $this->user->stripe_id,
                "original_source" => $card->stripe_id,
                "usage" => "single_use",
            ], ["stripe_account" => $store->settings->stripe_id]);

            $charge = \Stripe\Charge::create([
                "amount" => round($total * 100),
                "currency" => "usd",
                "source" => $storeSource,
                "application_fee" => round($preFeeTotal * $application_fee),
            ], ["stripe_account" => $store->settings->stripe_id]);

            $order = new Order;
            $order->user_id = $user->id;
            $order->customer_id = $customer->id;
            $order->store_id = $store->id;
            $order->order_number = substr(uniqid(rand(1, 9), false), 0, 12);
            $order->amount = $total;
            $order->deliveryFee = $deliveryFee;
            $order->processingFee = $processingFee;
            $order->mealPlanDiscount = $mealPlanDiscount;
            $order->fulfilled = false;
            $order->pickup = $request->get('pickup', 0);
            $order->delivery_date = date('Y-m-d', strtotime($deliveryDay));
            $order->save();

            foreach ($bag->getItems() as $item) {
                $mealOrder = new MealOrder();
                $mealOrder->order_id = $order->id;
                $mealOrder->store_id = $store->id;
                $mealOrder->meal_id = $item['meal']['id'];
                $mealOrder->quantity = $item['quantity'];
                $mealOrder->save();
            }

            // Send notification to store
            if ($store->settings->notificationEnabled('new_order')) {
                $store->sendNotification('new_order', $order);
            }

        } else {

            $plan = \Stripe\Plan::create([
                "amount" => round($total * 100),
                "interval" => "week",
                "product" => [
                    "name" => "Weekly subscription (" . $store->storeDetail->name . ")",
                ],
                "currency" => "usd",
            ], ['stripe_account' => $store->settings->stripe_id]);

            $token = \Stripe\Token::create([
                "customer" => $this->user->stripe_id,
            ], ['stripe_account' => $store->settings->stripe_id]);

            $storeSource = $storeCustomer->sources->create([
                'source' => $token,
            ], ['stripe_account' => $store->settings->stripe_id]);

            $subscription = $storeCustomer->subscriptions->create([
                'default_source' => $storeSource,
                'items' => [
                    ['plan' => $plan],
                ],
                'application_fee_percent' => $application_fee,
            ], ['stripe_account' => $store->settings->stripe_id]);

            $userSubscription = new Subscription();
            $userSubscription->user_id = $user->id;
            $userSubscription->customer_id = $customer->id;
            $userSubscription->stripe_customer_id = $storeCustomer->id;
            $userSubscription->store_id = $store->id;
            $userSubscription->name = "Weekly subscription (" . $store->storeDetail->name . ")";
            $userSubscription->stripe_id = substr($subscription->id,4);
            $userSubscription->stripe_plan = $plan->id;
            $userSubscription->quantity = 1;
            $userSubscription->amount = $total;
            $userSubscription->interval = 'week';
            $userSubscription->delivery_day = date('N', strtotime($deliveryDay));
            $userSubscription->next_renewal_at = Carbon::now('utc')->addDays(7);
            $userSubscription->save();

            // Create initial order
            $order = new Order;
            $order->user_id = $user->id;
            $order->customer_id = $customer->id;
            $order->store_id = $store->id;
            $order->subscription_id = $userSubscription->id;
            $order->order_number = substr($subscription->id,4) . '_1';
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
                $mealOrder->save();
            }

            // Send notification to store
            if ($store->settings->notificationEnabled('new_subscription')) {
                $store->sendNotification('new_subscription', $userSubscription);
            }
        }

        // Send notification
        $email = new NewOrder([
            'order' => $order ?? null,
            'subscription' => $userSubscription ?? null,
        ]);
        Mail::to($user)->send($email);

        /*
    $subscription = $user->newSubscription('main', $plan->id)->create($stripeToken['id']);
    $subscription->store_id = $this->store->id;
    $subscription->amount = $total;
    $subscription->interval = 'week';*/
    }
}
