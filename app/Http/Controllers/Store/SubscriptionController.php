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

class SubscriptionController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store
            ->subscriptions()
            ->with(['user:id', 'pickup_location'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->store
            ->subscriptions()
            ->with([
                'user',
                'user.userDetail',
                'orders',
                'orders.meals',
                'pickup_location'
            ])
            ->where('id', $id)
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
                    'error' => 'Meal plan not found'
                ],
                404
            );
        }

        try {
            $sub->cancel();
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to cancel Meal Plan'
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
                    'error' => 'Meal plan not found'
                ],
                404
            );
        }

        try {
            $sub->pause();
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to pause Meal Plan'
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
                    'error' => 'Meal plan not found'
                ],
                404
            );
        }

        try {
            $sub->resume();
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to resume Meal Plan'
                ],
                500
            );
        }
    }

    public function updateMeals(Request $request)
    {
        $id = intval($request->get('subscriptionId'));
        $sub = $this->store->subscriptions()->find($id);

        if (!$sub) {
            return response()->json(
                [
                    'error' => 'Meal plan not found'
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
                    'error' => 'Meal plan not found at payment gateway'
                ],
                404
            );
        }

        $bag = new Bag($request->get('bag'), $store);

        $application_fee = $store->settings->application_fee;
        $total = $bag->getTotal();
        $afterDiscountBeforeFees = $bag->getTotal();
        $preFeePreDiscount = $bag->getTotal();

        $deliveryFee = 0;
        $processingFee = 0;
        $mealPlanDiscount = 0;
        $salesTaxRate = $request->get('salesTaxRate');

        if ($store->settings->applyMealPlanDiscount) {
            $discount = $store->settings->mealPlanDiscount / 100;
            $mealPlanDiscount = $total * $discount;
            $total -= $mealPlanDiscount;
            $afterDiscountBeforeFees = $total;
        }

        if ($store->settings->applyDeliveryFee) {
            $total += $store->settings->deliveryFee;
            $deliveryFee += $store->settings->deliveryFee;
        }

        if ($store->settings->applyProcessingFee) {
            $total += $store->settings->processingFee;
            $processingFee += $store->settings->processingFee;
        }

        $salesTax = $total * $salesTaxRate;
        $total += $salesTax;

        // Delete existing stripe plan
        try {
            $plan = \Stripe\Plan::retrieve($sub->stripe_plan, [
                'stripe_account' => $sub->store->settings->stripe_id
            ]);
            $plan->delete();
        } catch (\Exception $e) {
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
        foreach ($bag->getItems() as $item) {
            $mealSub = new MealSubscription();
            $mealSub->subscription_id = $sub->id;
            $mealSub->store_id = $store->id;
            $mealSub->meal_id = $item['meal']['id'];
            $mealSub->quantity = $item['quantity'];
            if (isset($item['size']) && $item['size']) {
                $mealSub->meal_size_id = $item['size']['id'];
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

        // Update subscription pricing
        $sub->preFeePreDiscount = $preFeePreDiscount;
        $sub->mealPlanDiscount = $mealPlanDiscount;
        $sub->afterDiscountBeforeFees = $afterDiscountBeforeFees;
        $sub->processingFee = $processingFee;
        $sub->deliveryFee = $deliveryFee;
        $sub->salesTax = $salesTax;
        $sub->amount = $total;
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
            $order->salesTax = $salesTax;
            $order->amount = $total;
            $order->save();

            // Replace order meals
            $order->meal_orders()->delete();
            foreach ($bag->getItems() as $item) {
                $mealOrder = new MealOrder();
                $mealOrder->order_id = $order->id;
                $mealOrder->store_id = $store->id;
                $mealOrder->meal_id = $item['meal']['id'];
                $mealOrder->quantity = $item['quantity'];
                if (isset($item['size']) && $item['size']) {
                    $mealOrder->meal_size_id = $item['size']['id'];
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
        }
    }
}
