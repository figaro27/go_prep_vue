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
use App\Store;
use App\MealPackage;
use App\MealPackageSize;
use App\MealPackageSubscription;
use App\MealPackageOrder;
use App\Subscription;
use App\PurchasedGiftCard;
use App\Error;
use App\PickupLocation;

class SubscriptionController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subscriptions = $this->store
            ->subscriptions()
            ->where('status', '!=', 'cancelled')
            ->with(['user:id', 'pickup_location'])
            ->orderBy('created_at')
            ->get();

        $subscriptions->makeHidden([
            'latest_order',
            'latest_paid_order',
            'next_order',
            'meal_ids',
            'meal_quantities',
            'store',
            'orders',
            'items',
            'meal_package_items'
        ]);

        return $subscriptions;
    }

    public function cancelledSubscriptions()
    {
        $subscriptions = $this->store
            ->subscriptions()
            ->where('status', 'cancelled')
            ->with(['user:id', 'pickup_location'])
            ->orderBy('created_at')
            ->get();

        $subscriptions->makeHidden([
            'latest_order',
            'latest_paid_order',
            'next_order',
            'meal_ids',
            'meal_quantities',
            'store',
            'orders',
            'items',
            'meal_package_items'
        ]);

        return $subscriptions;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Subscription::where('id', $id)
            ->with(['pickup_location', 'user'])
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
                    'error' => 'Subscription not found'
                ],
                404
            );
        }

        if ($sub->prepaid) {
            if ($sub->renewalCount % $sub->prepaidWeeks === 0) {
                $sub->cancel();
            } else {
                try {
                    $sub->cancel();
                } catch (\Exception $e) {
                    return response()->json(
                        [
                            'error' => 'Failed to cancel Subscription'
                        ],
                        500
                    );
                }
            }
            return;
        }

        try {
            $sub->cancel();
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to cancel Subscription'
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
                    'error' => 'Subscription not found'
                ],
                404
            );
        }

        try {
            $sub->pause();
            return $sub;
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to pause Subscription'
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
                    'error' => 'Subscription not found'
                ],
                404
            );
        }

        try {
            $sub->resume();
            return $sub;
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to resume Subscription'
                ],
                500
            );
        }
    }

    public function updateNotes(Request $request)
    {
        $sub = Subscription::where('id', $request->get('id'))->first();
        $sub->notes = $request->get('notes');
        $sub->publicNotes = $request->get('publicNotes');
        $sub->update();

        $orders = $sub->orders->where('delivery_date', '>=', Carbon::now());
        foreach ($orders as $order) {
            $order->notes = $request->get('notes');
            $order->publicNotes = $request->get('publicNotes');
            $order->update();
        }
    }

    public function renew(Request $request)
    {
        $sub = Subscription::where('id', $request->get('id'))
            ->with(['pickup_location', 'user'])
            ->first();
        $sub->renew(true);
        return $sub;
    }

    public function updateRenewal(Request $request)
    {
        $sub = Subscription::where('id', $request->get('id'))->first();
        $date = $request->get('date')
            ? $request->get('date')
            : Carbon($sub->next_renewal_at)->toDateString();
        $time = $request->get('time')
            ? $request->get('time')
            : Carbon($sub->next_renewal_at)->toTimeString();
        $dateTime = $date . ' ' . $time;
        $dateTime = new Carbon($dateTime);
        $dateTime = $dateTime->setTimezone('utc')->toDateTimeString();
        $sub->next_renewal_at = $dateTime;
        $sub->renewal_updated = Carbon::now();
        $sub->update();
        return $sub;
    }

    public function updateMeals(Request $request)
    {
        try {
            $id = intval($request->get('subscriptionId'));
            $sub = $this->store->subscriptions()->find($id);

            if (!$sub) {
                return response()->json(
                    [
                        'error' => 'Subscription not found.'
                    ],
                    404
                );
            }
            $store = $sub->store;

            $user = auth('api')->user();
            $storeId = $request->get('store_id');
            $store = Store::with(['settings', 'storeDetail'])->findOrFail(
                $storeId
            );
            $storeName = strtolower($store->storeDetail->name);
            $bagItems = $request->get('bag');
            $bag = new Bag($request->get('bag'), $store);

            if (count($bagItems) == 0) {
                return response()->json(
                    [
                        'message' =>
                            'Your subscription must have at least one item.'
                    ],
                    400
                );
            }

            // Don't allow gift cards on subscription adjustments
            foreach ($bag->getItems() as $item) {
                if (
                    isset($item['meal']) &&
                    isset($item['meal']['gift_card']) &&
                    $item['meal']['gift_card'] === true
                ) {
                    return response()->json(
                        [
                            'message' =>
                                'Gift cards are not allowed on subscriptions since the subscription will renew and charge the customer for this gift card again next week. Please create a separate one time order to purchase a gift card.'
                        ],
                        400
                    );
                }
            }

            $weeklyPlan = $request->get('plan');
            $pickup = $request->get('pickup');
            $shipping = $request->get('shipping');
            $deliveryDay = $sub->delivery_day;
            $couponId = $request->get('coupon_id');
            $couponReduction = $request->get('couponReduction');
            $couponCode = $request->get('couponCode');
            $deliveryFee = $request->get('deliveryFee');
            $pickupLocation = $request->get('pickupLocation');
            //$stripeToken = $request->get('token');

            $application_fee = $store->settings->application_fee;
            $gratuity = $request->get('gratuity');
            $coolerDeposit = $request->get('coolerDeposit');
            $total = $request->get('subtotal');
            $subtotal = $request->get('subtotal');
            $afterDiscountBeforeFees = $request->get('afterDiscount');
            $preFeePreDiscount = $subtotal;
            $deposit = $request->get('deposit') / 100;

            $processingFee = $request->get('processingFee');
            $mealPlanDiscount = $request->get('mealPlanDiscount');
            $salesTax = $request->get('salesTax');
            $cardId = $request->get('card_id');

            $promotionReduction = $request->get('promotionReduction');
            $appliedReferralId = $request->get('applied_referral_id');
            $referralReduction = $request->get('referralReduction');
            $pointsReduction = $request->get('pointsReduction');
            $purchasedGiftCardId = $request->get('purchased_gift_card_id');
            $purchasedGiftCardReduction = $request->get(
                'purchasedGiftCardReduction'
            )
                ? $request->get('purchasedGiftCardReduction')
                : 0;

            $customerId = $request->get('customer');
            $customer = Customer::where('id', $customerId)->first();

            // $total += $salesTax;
            $total = $request->get('grandTotal');

            $notes = $request->get('notes');
            $publicNotes = $request->get('publicNotes');

            $cashOrder = $request->get('cashOrder');
            if ($cashOrder) {
                $cardId = null;
                $card = null;
            }

            // Update meals in subscription
            MealSubscription::where('subscription_id', $sub->id)->delete();
            MealPackageSubscription::where(
                'subscription_id',
                $sub->id
            )->delete();
            foreach ($bag->getItems() as $item) {
                $mealSub = new MealSubscription();
                $mealSub->subscription_id = $sub->id;
                $mealSub->store_id = $store->id;
                $mealSub->meal_id = $item['meal']['id'];
                $mealSub->quantity = $item['quantity'];
                $mealSub->price = $item['price'] * $item['quantity'];
                $mealSub->added_price = isset($item['added_price'])
                    ? $item['added_price'] * $item['quantity']
                    : 0;
                if (isset($item['free'])) {
                    $mealSub->free = $item['free'];
                }
                if (isset($item['size']) && $item['size']) {
                    $mealSub->meal_size_id = $item['size']['id'];
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
                            'subscription_id' => $sub->id,
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
                        $mealPackageSubscription->subscription_id = $sub->id;
                        $mealPackageSubscription->meal_package_id =
                            $item['meal_package_id'];
                        $mealPackageSubscription->meal_package_size_id =
                            $item['meal_package_size_id'];
                        $mealPackageSubscription->quantity =
                            $item['package_quantity'];
                        $mealPackageSubscription->price =
                            $item['package_price'];
                        if (
                            isset($item['delivery_day']) &&
                            $item['delivery_day']
                        ) {
                            $mealPackageSubscription->delivery_date =
                                $item['delivery_day']['day_friendly'];
                        }
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
                        $mealPackageSubscription->category_id = isset(
                            $item['category_id']
                        )
                            ? $item['category_id']
                            : null;
                        $mealPackageSubscription->items_quantity =
                            $mealSub->quantity;
                        $mealPackageSubscription->save();

                        $mealSub->meal_package_subscription_id =
                            $mealPackageSubscription->id;
                    } else {
                        $mealSub->meal_package_subscription_id = MealPackageSubscription::where(
                            [
                                'meal_package_id' => $item['meal_package_id'],
                                'meal_package_size_id' =>
                                    $item['meal_package_size_id'],
                                'subscription_id' => $sub->id,
                                'mappingId' => isset($item['mappingId'])
                                    ? $item['mappingId']
                                    : null
                            ]
                        )
                            ->pluck('id')
                            ->first();

                        $mealPackageSubscription->items_quantity +=
                            $mealSub->quantity;
                        $mealPackageSubscription->update();
                    }
                }

                if (isset($item['delivery_day']) && $item['delivery_day']) {
                    $mealSub->delivery_date =
                        $item['delivery_day']['day_friendly'];
                }
                $mealSub->category_id = isset($item['meal']['category_id'])
                    ? $item['meal']['category_id']
                    : null;
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

            $sub->store_id = $store->id;
            $sub->card_id = $cardId;
            $sub->preFeePreDiscount = $preFeePreDiscount;
            $sub->mealPlanDiscount = $mealPlanDiscount;
            $sub->afterDiscountBeforeFees = $afterDiscountBeforeFees;
            $sub->deliveryFee = $deliveryFee;
            $sub->processingFee = $processingFee;
            $sub->salesTax = $salesTax;
            $sub->gratuity = $gratuity;
            $sub->coolerDeposit = $coolerDeposit;
            $sub->amount = $total;
            $sub->pickup = $request->get('pickup', 0);
            $sub->shipping = $shipping;
            $sub->delivery_day = $deliveryDay;
            $sub->coupon_id = $couponId;
            $sub->couponReduction = $couponReduction;
            $sub->couponCode = $couponCode;
            $sub->pickup_location_id = $pickupLocation;
            $sub->referralReduction = $referralReduction;
            $sub->purchased_gift_card_id = $purchasedGiftCardId;
            $sub->purchasedGiftCardReduction = $purchasedGiftCardReduction;
            $sub->promotionReduction = $promotionReduction;
            $sub->pointsReduction = $pointsReduction;
            $sub->store_updated = Carbon::now(
                $this->store->settings->timezone
            )->toDateTimeString();
            $sub->notes = $notes;
            $sub->publicNotes = $publicNotes;
            $sub->save();

            // Update future orders IF cutoff hasn't passed yet
            $futureOrders = $sub
                ->orders()
                ->where([['fulfilled', 0], ['paid', 0]])
                ->whereDate('delivery_date', '>=', Carbon::now())
                ->get();

            foreach ($futureOrders as $order) {
                // Cutoff already passed. Missed your chance bud!
                $customDD = null;
                if (
                    $store->modules->customDeliveryDays ||
                    $store->modules->multipleDeliveryDays
                ) {
                    $weekIndex = date('N', strtotime($order->delivery_date));
                    $customDD = $store
                        ->deliveryDays()
                        ->where([
                            'day' => $weekIndex,
                            'type' => $sub->pickup ? 'pickup' : 'delivery'
                        ])
                        ->first();
                }
                if ($order->getCutoffDate($customDD)->isPast()) {
                    continue;
                }

                // Update order pricing
                $order->preFeePreDiscount = $preFeePreDiscount;
                $order->mealPlanDiscount = $mealPlanDiscount;
                $order->afterDiscountBeforeFees = $afterDiscountBeforeFees;
                $order->processingFee = $processingFee;
                $order->deliveryFee = $deliveryFee;
                $order->gratuity = $gratuity;
                $order->coolerDeposit = $coolerDeposit;
                $order->coupon_id = $couponId;
                $order->couponReduction = $couponReduction;
                $order->couponCode = $couponCode;
                $order->salesTax = $salesTax;
                $order->referralReduction = $referralReduction;
                $order->purchased_gift_card_id = $purchasedGiftCardId;
                $order->purchasedGiftCardReduction = $purchasedGiftCardReduction;
                $order->promotionReduction = $promotionReduction;
                $order->pointsReduction = $pointsReduction;
                $order->amount = $total;
                $order->shipping = $shipping;
                $order->pickup = $pickup;
                $order->pickup_location_id = $pickupLocation;
                $order->publicNotes = $publicNotes;
                $order->pickup_location_name = PickupLocation::where(
                    'id',
                    $pickupLocation
                )
                    ->pluck('name')
                    ->first();
                $order->purchased_gift_card_code = PurchasedGiftCard::where(
                    'id',
                    $purchasedGiftCardId
                )
                    ->pluck('code')
                    ->first();
                $order->store_name = $store->details->name;
                $order->transfer_type = ($shipping
                        ? 'Shipping'
                        : $pickup)
                    ? 'Pickup'
                    : 'Delivery';
                $customer = Customer::where('id', $sub->customer_id)->first();
                $order->customer_name = $customer->name;
                $order->customer_address = $customer->address;
                $order->customer_zip = $customer->zip;
                $goPrepFee =
                    $sub->afterDiscountBeforeFees *
                    ($store->settings->application_fee / 100);
                $stripeFee =
                    !$sub->cashOrder && $sub->amount > 0.5
                        ? $sub->amount * 0.029 + 0.3
                        : 0;
                $order->goprep_fee = $goPrepFee;
                $order->stripe_fee = $stripeFee;
                $order->grandTotal = $sub->amount - $goPrepFee - $stripeFee;
                $order->save();

                // Replace order meals && meal packages
                $order->meal_orders()->delete();

                $mealPackageOrders = MealPackageOrder::where(
                    'order_id',
                    $order->id
                )->get();
                foreach ($mealPackageOrders as $mealPackageOrder) {
                    $mealPackageOrder->delete();
                }

                foreach ($bag->getItems() as $item) {
                    $mealOrder = new MealOrder();
                    $mealOrder->order_id = $order->id;
                    $mealOrder->store_id = $store->id;
                    $mealOrder->meal_id = $item['meal']['id'];
                    $mealOrder->quantity = $item['quantity'];
                    $mealOrder->price = $item['price'] * $item['quantity'];
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
                        $mealOrder->added_price = isset($item['added_price'])
                            ? $item['added_price'] * $item['quantity']
                            : 0;
                    }

                    if (isset($item['meal_package_title'])) {
                        $mealOrder->meal_package_title =
                            $item['meal_package_title'];
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

                    if ($item['meal_package']) {
                        $mealOrder->meal_package = $item['meal_package'];
                        $mealOrder->added_price = isset($item['added_price'])
                            ? $item['added_price'] * $item['quantity']
                            : 0;
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
                                'delivery_date' =>
                                    $item['delivery_day']['day_friendly'],
                                'customTitle' => $item['customTitle'],
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
                            $mealPackageOrder->price = $item['package_price'];
                            if (
                                isset($item['delivery_day']) &&
                                $item['delivery_day']
                            ) {
                                $mealPackageOrder->delivery_date =
                                    $item['delivery_day']['day_friendly'];
                            }
                            $mealPackageOrder->customTitle =
                                $item['customTitle'];
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
                            $mealPackageOrder->items_quantity =
                                $mealOrder->quantity;
                            $mealPackageOrder->save();
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

                            $mealPackageOrder->items_quantity +=
                                $mealOrder->quantity;
                            $mealPackageOrder->update();
                        }
                    }
                    if ($item['meal_package'] === true && $mealPackageOrder) {
                        $mealOrder->meal_package_order_id =
                            $mealPackageOrder->id;
                        $mealOrder->update();
                    }
                }
            }
            if (isset($purchasedGiftCardId)) {
                $purchasedGiftCard = PurchasedGiftCard::where(
                    'id',
                    $purchasedGiftCardId
                )->first();
                $purchasedGiftCard->balance -= $purchasedGiftCardReduction;
                $purchasedGiftCard->update();
            }
        } catch (\Exception $e) {
            $error = new Error();
            $error->store_id = $store->id;
            $error->user_id = $this->user->id;
            $error->type = 'Updating Subscription';
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
