<?php

namespace App\Http\Controllers\User;

use App\Bag;
use App\Store;
use App\StoreDetail;
use App\Subscription;
use App\Order;
use App\MealOrder;
use App\Mail\Customer\NewOrder;
use Illuminate\Support\Facades\Mail;
use Auth;
use App\Http\Controllers\User\UserController;

class CheckoutController extends UserController
{
    public function checkout(\App\Http\Requests\CheckoutRequest $request)
    {
        $user = auth('api')->user();

        $bag = new Bag($request->get('bag'));
        $weeklyPlan = $request->get('plan');
        $pickup = $request->get('pickup');
        $deliveryDay = $request->get('delivery_day');
        $stripeToken = $request->get('token');
        $storeId = $request->get('store_id');

        $store = Store::with(['settings', 'storeDetail'])->findOrFail($storeId);

        $total = $bag->getTotal();
        if ($store->settings->applyDeliveryFee) {
            $total += $store->settings->deliveryFee;
        }

        \Stripe\Stripe::setApiKey($store->settings->stripe_account['access_token']);
        if (!$user->hasCustomer($store->id)) {
            $user->createCustomer($store->id, $stripeToken['id']);
        }
        $customer = $user->getCustomer($store->id);

        if (!$weeklyPlan) {
            $charge = \Stripe\Charge::create([
                "amount" => $total * 100,
                "currency" => "usd",
                "source" => $stripeToken['id'],
                "application_fee" => $total * 10, // 10%
            ], ["stripe_account" => $store->settings->stripe_id]);

            $order = new Order;
            $order->user_id = $user->id;
            $order->store_id = $store->id;
            $order->order_number = $charge->id;
            $order->amount = $total;
            $order->fulfilled = false;
            $order->delivery_date = date('Y-m-d', strtotime($deliveryDay));
            $order->save();

            foreach($bag->getItems() as $item) {
              $mealOrder = new MealOrder();
              $mealOrder->order_id = $order->id;
              $mealOrder->store_id = $store->id;
              $mealOrder->meal_id = $item['meal']['id'];
              $mealOrder->quantity = $item['quantity'];
              $mealOrder->save();
            }

            // Send notification to store
            if($store->settings->notificationEnabled('new_order')) {
              $store->sendNotification('new_order', $order);
            }

        } else {
            $plan = \Stripe\Plan::create([
                "amount" => $total * 100,
                "interval" => "week",
                "product" => [
                    "name" => "Weekly subscription (" . $store->storeDetail->name . ")",
                ],
                "currency" => "usd",
            ]);

            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->stripe_id,
                'items' => [
                    ['plan' => $plan->id],
                ],
                'application_fee_percent' => 10,
            ], ['stripe_account' => $store->settings->stripe_id]);

            $userSubscription = new Subscription();
            $userSubscription->user_id = $user->id;
            $userSubscription->store_id = $store->id;
            $userSubscription->name = "Weekly subscription (" . $store->storeDetail->name . ")";
            $userSubscription->stripe_id = $subscription->id;
            $userSubscription->stripe_plan = $plan->id;
            $userSubscription->quantity = 1;
            $userSubscription->amount = $total;
            $userSubscription->interval = 'week';
            $userSubscription->delivery_day = date('N', strtotime($deliveryDay)); 
            $userSubscription->save();

            // Create initial order
            $order = new Order;
            $order->user_id = $user->id;
            $order->store_id = $store->id;
            $order->subscription_id = $userSubscription->id;
            $order->order_number = $subscription->id.'_1';
            $order->amount = $total;
            $order->fulfilled = false;
            $order->delivery_date = date('Y-m-d', strtotime($deliveryDay));
            $order->save();

            foreach($bag->getItems() as $item) {
              $mealOrder = new MealOrder();
              $mealOrder->order_id = $order->id;
              $mealOrder->store_id = $store->id;
              $mealOrder->meal_id = $item['meal']['id'];
              $mealOrder->quantity = $item['quantity'];
              $mealOrder->save();
            }

            // Send notification to store
            if($store->settings->notificationEnabled('new_subscription')) {
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
