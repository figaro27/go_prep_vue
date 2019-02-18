<?php

namespace App\Http\Controllers\User;

use App\Bag;
use App\MealSubscription;
use Illuminate\Http\Request;

class SubscriptionController extends UserController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->user->subscriptions()->with(['orders', 'meals'])->get();
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
            return response()->json([
                'error' => 'Meal plan not found',
            ], 404);
        }

        $sub->cancel();
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
            return response()->json([
                'error' => 'Meal plan not found',
            ], 404);
        }

        try {
            $sub->pause();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to pause Meal Plan',
            ], 404);
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

        if (!$sub) {
            return response()->json([
                'error' => 'Meal plan not found',
            ], 404);
        }

        try {
            $sub->resume();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to resume Meal Plan',
            ], 404);
        }
    }

    /**
     * Update meals
     *
     * @return \Illuminate\Http\Response
     */
    public function updateMeals(Request $request, $id)
    {
        $sub = $this->user->subscriptions()->find($id);

        if (!$sub) {
            return response()->json([
                'error' => 'Meal plan not found',
            ], 404);
        }
        $store = $sub->store;

        try {
            $subscription = \Stripe\Subscription::retrieve('sub_' . $sub->stripe_id, ['stripe_account' => $store->settings->stripe_id]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Meal plan not found at payment gateway',
            ], 404);
        }

        $bag = new Bag($request->get('bag'));
        $application_fee = $store->settings->application_fee;
        $total = $bag->getTotal();

        MealSubscription::where('subscription_id', $sub->id)->delete();
        foreach ($bag->getItems() as $item) {
            $mealSub = new MealSubscription();
            $mealSub->subscription_id = $sub->id;
            $mealSub->store_id = $store->id;
            $mealSub->meal_id = $item['meal']['id'];
            $mealSub->quantity = $item['quantity'];
            $mealSub->save();
        }

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

        if ($store->settings->applyMealPlanDiscount && $weeklyPlan) {
            $discount = $store->settings->mealPlanDiscount / 100;
            $total -= ($afterDiscountBeforeFees * $discount);
            $afterDiscountBeforeFees -= ($afterDiscountBeforeFees * $discount);
            $mealPlanDiscount = (($store->settings->mealPlanDiscount / 100) * $afterDiscountBeforeFees);
        }

        try {
            $plan = \Stripe\Plan::retrieve($sub->stripe_plan, ['stripe_account' => $sub->store->settings->stripe_id]);
            $plan->delete();
        } catch (\Exception $e) {

        }

        $sub->amount = $total;
        $sub->save();

        $plan = \Stripe\Plan::create([
            "amount" => round($total * 100),
            "interval" => "week",
            "product" => [
                "name" => "Weekly subscription (" . $store->storeDetail->name . ")",
            ],
            "currency" => "usd",
        ], ['stripe_account' => $store->settings->stripe_id]);

        \Stripe\Subscription::update($subscription->id, [
            'cancel_at_period_end' => false,
            'items' => [
                [
                    'id' => $subscription->items->data[0]->id,
                    'plan' => $plan->id,
                ],
            ],
        ], ['stripe_account' => $store->settings->stripe_id]);

    }
}
