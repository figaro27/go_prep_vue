<?php

namespace App;

use App\Meal;
use App\MealOrder;
use App\MealOrderComponent;
use App\MealOrderAddon;
use App\MealPackageOrder;
use App\MealPackageSubscription;
use App\MealAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Coupon;
use App\OrderTransaction;

class Subscription extends Model
{
    protected $fillable = ['status', 'cancelled_at', 'weekCount'];

    protected $appends = [
        'store_name',
        // 'latest_order',
        // 'latest_paid_order',
        // 'latest_unpaid_order',
        'next_delivery_date',
        // 'next_unpaid_delivery_date',
        'next_order',
        // 'meal_ids',
        // 'meal_quantities',
        // 'pre_coupon',
        'items',
        'meal_package_items',
        'interval_title',
        'paid_order_count'
        // 'total_item_quantity'
    ];

    protected $casts = [
        'next_renewal_at' => 'date',
        'preFeePreDiscount' => 'float',
        'afterDiscountBeforeFees' => 'float',
        'processingFee' => 'float',
        'deliveryFee' => 'float',
        'amount' => 'float',
        'salesTax' => 'float',
        'mealPlanDiscount' => 'float',
        'monthlyPrepay' => 'boolean',
        'mealsReplaced' => 'boolean',
        'gratuity' => 'float'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function meals()
    {
        return $this->belongsToMany('App\Meal', 'meal_subscriptions')
            ->withPivot('quantity', 'meal_size_id')
            ->withTrashed()
            ->using('App\MealSubscription');
    }

    public function meal_subscriptions()
    {
        return $this->hasMany('App\MealSubscription');
    }

    public function meal_package_subscriptions()
    {
        return $this->hasMany('App\MealPackageSubscription');
    }

    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    public function coupon()
    {
        return $this->hasOne('App\Coupon');
    }

    public function pickup_location()
    {
        return $this->belongsTo('App\PickupLocation');
    }

    public function getPreCouponAttribute()
    {
        return $this->amount + $this->couponReduction;
    }

    public function getLatestOrderAttribute()
    {
        return $this->orders()
            ->orderBy('delivery_date', 'desc')
            ->first();
    }

    public function getLatestUnpaidMDOrder($futureDeliveryDate = true)
    {
        $latestOrder = $this->orders()
            ->where('paid', 0)
            ->orderBy('id', 'desc');

        if ($futureDeliveryDate) {
            $latestOrder = $latestOrder->whereHas('meal_orders', function (
                $query
            ) {
                $query->whereNotNull('meal_orders.delivery_date');

                $query->whereDate(
                    'meal_orders.delivery_date',
                    '>=',
                    Carbon::now()
                );
            });
        }

        return $latestOrder->first();
    }

    /**
     * Returns the most recent unpaid order.
     *
     * @param boolean $futureDeliveryDate
     * @return App\Order
     */

    public function getLatestUnpaidOrder($futureDeliveryDate = true)
    {
        $latestOrder = $this->orders()
            ->where('paid', 0)
            ->orderBy('delivery_date', 'desc');

        if ($futureDeliveryDate) {
            $latestOrder = $latestOrder->whereDate(
                'delivery_date',
                '>=',
                Carbon::now()
            );
        }

        return $latestOrder->first();
    }

    public function getLatestUnpaidOrderAttribute($futureDeliveryDate = true)
    {
        $latestOrder = $this->orders()
            ->where('paid', 0)
            ->orderBy('delivery_date', 'desc');

        if ($futureDeliveryDate) {
            $latestOrder = $latestOrder->whereDate(
                'delivery_date',
                '>=',
                Carbon::now()
            );
        }

        return $latestOrder->first();
    }

    public function getLatestPaidOrderAttribute()
    {
        $latestOrder = $this->orders()
            ->where('paid', 1)
            ->orderBy('delivery_date', 'desc')
            ->first();

        $arr = (array) $latestOrder;

        if (!empty($arr)) {
            return $latestOrder;
        } else {
            return $this->orders()
                ->orderBy('delivery_date', 'desc')
                ->first();
        }
    }

    public function getNextDeliveryDateAttribute()
    {
        if ($this->latest_order) {
            $date = new Carbon(
                $this->latest_paid_order->delivery_date->toDateTimeString()
            );

            if ($date->isFuture()) {
                return $date;
            }
            return $date->addWeek();
        }

        // Catch all
        return $this->store->getNextDeliveryDay($this->delivery_day);
    }

    public function getNextUnpaidDeliveryDateAttribute()
    {
        if ($this->latest_unpaid_order) {
            $date = new Carbon(
                $this->latest_unpaid_order->delivery_date->toDateTimeString()
            );
            return $date;
        }
    }

    public function getNextOrderAttribute()
    {
        if (
            $this->latest_paid_order &&
            $this->latest_paid_order->delivery_date > Carbon::now()
        ) {
            return $this->latest_paid_order;
        } else {
            return $this->latest_unpaid_order;
        }
    }

    public function getIntervalTitleAttribute()
    {
        switch ($this->interval) {
            case 'day':
                return 'Daily';
                break;

            case 'week':
                return 'Weekly';
                break;

            case 'biweek':
                return 'Bi-Weekly';
                break;

            case 'month':
                return 'Monthly';
                break;
        }
    }

    public function getMealIdsAttribute()
    {
        return $this->meals()
            ->get()
            ->pluck('id');
    }
    public function getMealQuantitiesAttribute()
    {
        return $this->meals()
            ->get()
            ->keyBy('id')
            ->map(function ($meal) {
                return $meal->pivot->quantity ?? 0;
            });
    }

    public function getStoreNameAttribute()
    {
        return $this->store->storeDetail->name;
    }

    public function getMealPackageItemsAttribute()
    {
        return $this->meal_package_subscriptions()
            ->with(['meal_package', 'meal_package_size'])
            ->get();
    }

    public function getItemsAttribute()
    {
        return $this->meal_subscriptions()
            ->with([
                'components',
                'components.component',
                'components.option',
                'addons'
            ])
            ->get()
            ->map(function ($mealSub) {
                return (object) [
                    'meal_id' => $mealSub->meal_id,
                    'delivery_date' => $mealSub->delivery_date,
                    'meal_size_id' => $mealSub->meal_size_id,
                    'meal_title' => $mealSub->title,
                    'full_title' => $mealSub->full_title,
                    'title' => $mealSub->title,
                    'html_title' => $mealSub->html_title,
                    'customTitle' => $mealSub->customTitle,
                    'customSize' => $mealSub->customSize,
                    'quantity' => $mealSub->quantity,
                    'unit_price' => $mealSub->unit_price,
                    'price' => $mealSub->price
                        ? $mealSub->price
                        : $mealSub->unit_price * $mealSub->quantity,
                    'free' => $mealSub->free,
                    'special_instructions' => $mealSub->special_instructions,
                    'meal_package_subscription_id' =>
                        $mealSub->meal_package_subscription_id,
                    'components' => $mealSub->components->map(function (
                        $component
                    ) {
                        return (object) [
                            'meal_component_id' => $component->component
                                ? $component->component->id
                                : null,
                            'meal_component_option_id' => $component->option
                                ? $component->option->id
                                : null,
                            'component' => $component->component
                                ? $component->component->title
                                : null,
                            'option' => $component->option
                                ? $component->option->title
                                : null
                        ];
                    }),
                    'addons' => $mealSub->addons->map(function ($addon) {
                        return (object) [
                            'meal_addon_id' => isset($addon->addon->id)
                                ? $addon->addon->id
                                : null,
                            'addon' => isset($addon->addon->title)
                                ? $addon->addon->title
                                : null
                        ];
                    })
                ];
            });
    }

    public function isPaused()
    {
        return $this->status === 'paused';
    }

    /**
     * Renew the subscription
     *
     * @return boolean
     */
    public function renew(Collection $stripeInvoice, Collection $stripeEvent)
    {
        $isMultipleDelivery = (int) $this->isMultipleDelivery;

        $latestOrder = null;
        if ($isMultipleDelivery == 1) {
            $latestOrder = $this->getLatestUnpaidMDOrder();
        } else {
            $latestOrder = $this->getLatestUnpaidOrder();
        }

        if ($this->status != 'cancelled' && !$latestOrder) {
            throw new \Exception(
                'No unpaid order for subscription #' . $this->id
            );
        }

        // Ensure we haven't already processed this payment
        if (
            $this->orders()
                // ->where('stripe_id', $stripeInvoice->get('id'))
                ->where('stripe_id', $stripeInvoice->get('charge'))
                ->count() &&
            !$this->monthlyPrepay
        ) {
            return;
        }

        // Updating item stock
        if ($this->store->modules->stockManagement) {
            foreach ($this->meal_subscriptions as $mealSub) {
                $meal = Meal::where('id', $mealSub->meal_id)->first();
                if ($meal && $meal->stock !== null) {
                    if ($meal->stock === 0) {
                        $mealSub->delete();
                        $this->syncPrices();
                    } elseif ($meal->stock < $mealSub->quantity) {
                        $mealSub->quantity = $meal->stock;
                        $mealSub->update();
                        $meal->stock = 0;
                        $meal->active = 0;
                        $this->syncPrices();
                    } else {
                        $meal->stock -= $mealSub->quantity;
                        if ($meal->stock === 0) {
                            $meal->active = 0;
                        }
                    }
                    $meal->update();
                }
            }
        }

        // Retrieve the subscription from Stripe
        $subscription = \Stripe\Subscription::retrieve(
            'sub_' . $this->stripe_id,
            ['stripe_account' => $this->store->settings->stripe_id]
        );

        $latestOrder->paid = $this->status != 'paused' ? 1 : 0;
        $latestOrder->paid_at = new Carbon();
        $latestOrder->stripe_id = $stripeInvoice->get('charge', null);
        // $latestOrder->stripe_id = $stripeInvoice->get('id', null);
        $latestOrder->save();

        $latestOrder->events()->create([
            'type' => 'payment_succeeded',
            'stripe_event' => $stripeEvent
        ]);

        $order_transaction = new OrderTransaction();
        $order_transaction->order_id = $latestOrder->id;
        $order_transaction->store_id = $latestOrder->store_id;
        $order_transaction->user_id = $latestOrder->user_id;
        $order_transaction->customer_id = $latestOrder->customer_id;
        $order_transaction->type = 'order';
        // if (!$cashOrder) {
        //     $order_transaction->stripe_id = $latestOrder->stripe_id;
        //     $order_transaction->card_id = $latestOrder->card_id
        //         ? $latestOrder->card_id
        //         : null;
        // } else {
        //     $order_transaction->stripe_id = null;
        //     $order_transaction->card_id = null;
        // }
        $order_transaction->stripe_id = $latestOrder->stripe_id
            ? $latestOrder->stripe_id
            : null;
        $order_transaction->card_id = $latestOrder->card_id
            ? $latestOrder->card_id
            : null;
        $order_transaction->amount = $latestOrder->amount;
        $order_transaction->save();

        $this->syncPrices();

        // Create new order for next delivery
        $newOrder = new Order();

        $newOrder->user_id = $this->user_id;
        $newOrder->customer_id = $this->customer_id;
        $newOrder->card_id = $this->card_id ? $this->card_id : null;
        $newOrder->store_id = $this->store->id;
        $newOrder->subscription_id = $this->id;
        $newOrder->order_number =
            strtoupper(substr(uniqid(rand(10, 99), false), -4)) .
            chr(rand(65, 90)) .
            rand(10, 99);
        $newOrder->preFeePreDiscount = $this->preFeePreDiscount;
        $newOrder->mealPlanDiscount = $this->mealPlanDiscount;
        $newOrder->afterDiscountBeforeFees = $this->afterDiscountBeforeFees;
        $newOrder->deliveryFee = $this->deliveryFee;
        $newOrder->gratuity = $this->gratuity;
        $newOrder->coolerDeposit = $this->coolerDeposit;
        $newOrder->processingFee = $this->processingFee;
        $newOrder->salesTax = $this->salesTax;
        $newOrder->coupon_id = $this->coupon_id;
        $newOrder->couponReduction = $this->couponReduction;
        $newOrder->couponCode = $this->couponCode;
        $newOrder->referralReduction = $this->referralReduction;
        $newOrder->promotionReduction = $this->promotionReduction;
        $newOrder->pointsReduction = $this->pointsReduction;
        $newOrder->originalAmount = $this->amount;
        $newOrder->amount = $this->amount;
        $newOrder->currency = $this->currency;
        $newOrder->fulfilled = false;
        $newOrder->pickup = $this->pickup;

        $newOrder->pickup_location_id = $this->pickup_location_id;
        $newOrder->transferTime = $this->transferTime;

        // Refine this
        $newOrder->delivery_date =
            $this->interval === 'week'
                ? $latestOrder->delivery_date->addWeeks(1)
                : $latestOrder->delivery_date->addDays(30);
        $newOrder->isMultipleDelivery = $this->isMultipleDelivery;
        $newOrder->save();

        // Assign meal package orders from meal package subscriptions
        foreach ($this->meal_package_subscriptions as $mealPackageSub) {
            $mealPackageOrder = new MealPackageOrder();
            $mealPackageOrder->store_id = $this->store->id;
            $mealPackageOrder->order_id = $newOrder->id;
            $mealPackageOrder->meal_package_id =
                $mealPackageSub->meal_package_id;
            $mealPackageOrder->meal_package_size_id =
                $mealPackageSub->meal_package_size_id;
            $mealPackageOrder->quantity = $mealPackageSub->quantity;
            $mealPackageOrder->price = $mealPackageSub->price;
            $mealPackageOrder->customTitle = $mealPackageSub->customTitle;
            $mealPackageOrder->customSize = $mealPackageSub->customSize;
            $mealPackageOrder->save();
        }

        // Assign subscription meals to new order
        foreach ($this->fresh()->meal_subscriptions as $mealSub) {
            $mealOrder = new MealOrder();
            $mealOrder->order_id = $newOrder->id;
            $mealOrder->store_id = $this->store->id;
            $mealOrder->meal_id = $mealSub->meal_id;
            $mealOrder->meal_size_id = $mealSub->meal_size_id;
            $mealOrder->quantity = $mealSub->quantity;
            $mealOrder->price = $mealSub->price;
            $mealOrder->special_instructions = $mealSub->special_instructions;
            $mealOrder->meal_package = $mealSub->meal_package
                ? $mealSub->meal_package
                : 0;
            $mealOrder->free = $mealSub->free ? $mealSub->free : 0;
            $mealOrder->customTitle = $mealSub->customTitle;
            $mealOrder->customSize = $mealSub->customSize;

            if ($mealSub->meal_package_subscription_id !== null) {
                $mealPackageSub = MeaLPackageSubscription::where(
                    'id',
                    $mealSub->meal_package_subscription_id
                )->first();
                $mealOrder->meal_package_order_id = MealPackageOrder::where([
                    'meal_package_id' => $mealPackageSub->meal_package_id,
                    'meal_package_size_id' =>
                        $mealPackageSub->meal_package_size_id,
                    'order_id' => $newOrder->id
                ])
                    ->pluck('id')
                    ->first();
            }

            if ($isMultipleDelivery == 1 && $mealSub->delivery_date) {
                $mealSub->delivery_date =
                    $this->interval === 'week'
                        ? $mealSub->delivery_date->addWeeks(1)
                        : $mealSub->delivery_date->addDays(30);
                $mealSub->save();
                $mealOrder->delivery_date = $mealSub->delivery_date;
            }

            $mealOrder->save();

            if ($mealSub->has('components')) {
                foreach ($mealSub->components as $component) {
                    MealOrderComponent::create([
                        'meal_order_id' => $mealOrder->id,
                        'meal_component_id' => $component->meal_component_id,
                        'meal_component_option_id' =>
                            $component->meal_component_option_id
                    ]);
                }
            }

            if ($mealSub->has('addons')) {
                foreach ($mealSub->addons as $addon) {
                    MealOrderAddon::create([
                        'meal_order_id' => $mealOrder->id,
                        'meal_addon_id' => $addon->meal_addon_id
                    ]);
                }
            }

            // Update Meal Attachments using Explicits
            $attachments = MealAttachment::where(
                'meal_id',
                $mealSub->meal_id
            )->get();
            if ($attachments) {
                foreach ($attachments as $attachment) {
                    $mealOrder = new MealOrder();
                    $mealOrder->order_id = $order->id;
                    $mealOrder->store_id = $store->id;
                    $mealOrder->meal_id = $attachment->attached_meal_id;
                    $mealOrder->quantity =
                        $attachment->quantity * $item['quantity'];

                    if ($isMultipleDelivery == 1 && $mealSub->delivery_date) {
                        $mealOrder->delivery_date =
                            $this->interval === 'week'
                                ? $mealSub->delivery_date
                                    ->addWeeks(1)
                                    ->toDateString()
                                : $mealSub->delivery_date
                                    ->addDays(30)
                                    ->toDateString();
                    }

                    $mealOrder->save();
                }
            }
        }

        // Increment the week count by 1
        $this->update([
            'weekCount' => $this->weekCount + 1
        ]);

        // Only charge once per month on monthly prepay subscriptions
        if (
            $this->monthlyPrepay &&
            ($this->weekCount !== 0 || $this->weekCount % 4 !== 0)
        ) {
            $this->apply100offCoupon();
        } else {
            $this->remove100offCoupon();
        }

        // Cancelling the subscription for next month if cancelled_at is marked
        if (
            $this->monthlyPrepay &&
            $this->cancelled_at !== null &&
            $this->weekCount % 4 === 0
        ) {
            $this->cancel();
            return;
        }

        // Store next charge time as reported by Stripe
        $this->next_renewal_at = $subscription->current_period_end;
        $this->save();

        // Send new order notification to store at the cutoff once the order is paid
        if ($this->store->settings->notificationEnabled('new_order')) {
            $this->store->sendNotification('new_order', [
                'order' => $latestOrder ?? null,
                'pickup' => $latestOrder->pickup ?? null,
                'card' => null,
                'customer' => $latestOrder->customer ?? null,
                'subscription' => $this ?? null
            ]);
        }

        // Send new order notification to customer at the cutoff once the order is paid
        if ($this->user->details->notificationEnabled('new_order')) {
            $this->user->sendNotification('new_order', [
                'order' => $latestOrder ?? null,
                'pickup' => $latestOrder->pickup ?? null,
                'card' => null,
                'customer' => $latestOrder->customer ?? null,
                'subscription' => $this ?? null
            ]);
        }
    }

    public function syncDiscountPrices()
    {
        $promotions = $this->store->promotions;
        $pointsRate = 0;
        $this->promotionReduction = 0;
        foreach ($promotions as $promotion) {
            if ($promotion->active) {
                if ($promotion->promotionType === 'points') {
                    $pointsRate = $promotion->promotionAmount;
                }
                if ($promotion->conditionType === 'subtotal') {
                    if (
                        $this->preFeePreDiscount > $promotion->conditionAmount
                    ) {
                        if ($promotion->promotionType === 'flat') {
                            $this->promotionReduction +=
                                $promotion->promotionAmount;
                        } else {
                            $this->promotionReduction +=
                                ($promotion->promotionAmount / 100) *
                                $this->preFeePreDiscount;
                        }
                        if ($promotion->freeDelivery) {
                            $this->deliveryFee = 0;
                        }
                    }
                }
                if ($promotion->conditionType === 'meals') {
                    if (
                        $this->total_item_quantity > $promotion->condtionAmount
                    ) {
                        if ($promotion->promotionType === 'flat') {
                            $this->promotionReduction +=
                                $promotion->promotionAmount;
                        } else {
                            $this->promotionReduction +=
                                ($promotion->promotionAmount / 100) *
                                $this->preFeePreDiscount;
                        }
                        if ($promotion->freeDelivery) {
                            $this->deliveryFee = 0;
                        }
                    }
                }
                if ($promotion->conditionType === 'orders') {
                    $remainingPromotionOrders = 0;
                    $conditionAmount = $promotion->conditionAmount;
                    if ($conditionAmount > $this->user->orderCount) {
                        $remainingPromotionOrders =
                            $conditionAmount - $this->user->orderCount;
                    } else {
                        $increment = $conditionAmount;
                        while ($conditionAmount < $this->user->orderCount) {
                            $conditionAmount += $increment;
                        }
                        $remainingPromotionOrders =
                            $conditionAmount - $this->user->orderCount;
                    }
                    if ($remainingPromotionOrders === 0.0) {
                        if ($promotion->promotionType === 'flat') {
                            $this->promotionReduction +=
                                $promotion->promotionAmount;
                        } else {
                            $this->promotionReduction +=
                                ($promotion->promotionAmount / 100) *
                                $this->preFeePreDiscount;
                        }
                        if ($promotion->freeDelivery) {
                            $this->deliveryFee = 0;
                        }
                    }
                }
            }
            $this->update();
        }

        if ($pointsRate > 0) {
            $customer = Customer::where('id', $this->customer_id)->first();
            $customer->points -= $this->pointsReduction * 100;
            $customer->points += $this->afterDiscountBeforeFees * $pointsRate;
            $customer->update();
        }

        // Set points reduction on subscription to 0 after renewal since points were all used on first order
        $this->pointsReduction = 0;
        $this->update();

        // Check referral type and set referral reduction to 0 if the frequency type is not all orders.
        if (
            $this->store->referralSettings &&
            $this->store->referralSettings->frequency !== 'allOrders'
        ) {
            $this->referralReduction = 0;
            $this->update();
        }
    }

    public static function syncStock($meal)
    {
        $mealSubs = MealSubscription::where('meal_id', $meal->id)->get();
        foreach ($mealSubs as $mealSub) {
            $mealSub->delete();
            $mealSub->subscription->syncPrices();
            $mealSub->subscription->updateCurrentMealOrders();
        }
    }

    /**
     * Ensures subscription pricing is in line with the attached meals
     *
     * @return void
     */
    public function syncPrices($mealsReplaced = true)
    {
        try {
            $subscription = \Stripe\Subscription::retrieve(
                'sub_' . $this->stripe_id,
                ['stripe_account' => $this->store->settings->stripe_id]
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Subscription not found at payment gateway'
                ],
                404
            );
        }

        // Cancelled subscription. Halt here
        if ($subscription->status === \Stripe\Subscription::STATUS_CANCELED) {
            return;
        }

        $items = $this->fresh()->meal_subscriptions->map(function ($meal) {
            if (!$meal->meal_package) {
                // $price = $meal->meal_size
                //     ? $meal->meal_size->price
                //     : $meal->meal->price;
                // foreach ($meal->components as $component) {
                //     $price += $component->option->price;
                // }
                // foreach ($meal->addons as $addon) {
                //     $price += $addon->addon->price;
                // }

                // If errors, comment this line out below and uncomment out above. Then do the same with $prePackagePrice below
                $price = $meal->price;
                return [
                    'quantity' => $meal->quantity,
                    'meal' => $meal->meal,
                    'price' => $price
                ];
            }
        });

        $store = $this->store;

        $bag = new Bag($items, $store);

        // If errors reverse this
        // $prePackagePrice = $bag->getTotal();
        $prePackagePrice = $bag->getTotalSync();

        $totalPackagePrice = 0;
        foreach (
            $this->fresh()->meal_package_subscriptions
            as $mealPackageSub
        ) {
            $totalPackagePrice += $mealPackageSub->price;
        }

        $total = $prePackagePrice + $totalPackagePrice;
        $afterDiscountBeforeFees = $prePackagePrice + $totalPackagePrice;
        $preFeePreDiscount = $prePackagePrice + $totalPackagePrice;

        // Adjust the price of the subscription on renewal if a one time coupon code was used. (Remove coupon from subscription).
        $coupon = Coupon::where('id', $this->coupon_id)->first();
        if (isset($coupon) && $coupon->oneTime) {
            $this->coupon_id = null;
            $this->couponReduction = null;
            $this->couponCode = null;
            $this->update();
        } else {
            $afterDiscountBeforeFees -= $this->couponReduction;
            $total -= $this->couponReduction;
        }

        $deliveryFee = $this->deliveryFee;
        $processingFee = 0;
        $mealPlanDiscount = 0;
        $salesTaxRate =
            round(100 * ($this->salesTax / $this->afterDiscountBeforeFees), 2) /
                100 ??
            0;

        if ($this->store->settings->applyMealPlanDiscount) {
            $discount = $this->store->settings->mealPlanDiscount / 100;
            $mealPlanDiscount = $preFeePreDiscount * $discount;
            $total -= $mealPlanDiscount;
            $afterDiscountBeforeFees -= $mealPlanDiscount;
        }

        // if ($this->store->settings->applyDeliveryFee && !$this->pickup) {
        //     $total += $this->store->settings->deliveryFee;
        //     $deliveryFee += $this->store->settings->deliveryFee;
        // }
        $gratuity = $this->gratuity;
        $total += $gratuity;

        $coolerDeposit = $this->coolerDeposit;
        $total += $coolerDeposit;

        $total += $deliveryFee;

        if ($this->store->settings->applyProcessingFee) {
            if ($this->store->settings->processingFeeType === 'flat') {
                $total += $this->store->settings->processingFee;
                $processingFee += $this->store->settings->processingFee;
            } else {
                $processingFee +=
                    ($this->store->settings->processingFee / 100) *
                    $afterDiscountBeforeFees;
                $total += $processingFee;
            }
        }

        $salesTax = ceil($afterDiscountBeforeFees * $salesTaxRate * 100) / 100;
        $total += $salesTax;

        // Update subscription pricing
        $this->preFeePreDiscount = $preFeePreDiscount;
        $this->mealPlanDiscount = $mealPlanDiscount;
        $this->afterDiscountBeforeFees = $afterDiscountBeforeFees;
        $this->processingFee = $processingFee;
        $this->deliveryFee = $deliveryFee;
        $this->salesTax = $salesTax;

        $this->save();

        $this->syncDiscountPrices();
        $total -= $this->referralReduction;
        $total -= $this->promotionReduction;
        $total -= $this->pointsReduction;
        $this->gratuity = $gratuity;
        $this->coolerDeposit = $coolerDeposit;
        $this->amount = $total;
        $this->save();

        // Delete existing stripe plan
        try {
            $plan = \Stripe\Plan::retrieve($this->stripe_plan, [
                'stripe_account' => $this->store->settings->stripe_id
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
                        $this->store->storeDetail->name .
                        ")"
                ],
                "currency" => "usd"
            ],
            ['stripe_account' => $this->store->settings->stripe_id]
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
            ['stripe_account' => $this->store->settings->stripe_id]
        );

        // Assign new plan ID to subscription
        $this->stripe_plan = $plan->id;
        if ($mealsReplaced) {
            $this->mealsReplaced = 1;
        }
        $this->save();

        $this->updateCurrentMealOrders();
    }

    public function updateCurrentMealOrders()
    {
        $items = $this->fresh()->meal_subscriptions;

        $store = $this->store;

        $bag = new Bag($items, $store);
        // Update future orders IF cutoff hasn't passed yet
        $futureOrders = $this->orders()
            ->where([['fulfilled', 0], ['paid', 0]])
            ->whereDate('delivery_date', '>=', Carbon::now())
            ->get();

        foreach ($futureOrders as $order) {
            // Cutoff already passed. Missed your chance bud!
            if ($order->cutoff_passed) {
                continue;
            }

            // Update order pricing
            $order->preFeePreDiscount = $this->preFeePreDiscount;
            $order->mealPlanDiscount = $this->mealPlanDiscount;
            $order->afterDiscountBeforeFees = $this->afterDiscountBeforeFees;
            $order->processingFee = $this->processingFee;
            $order->deliveryFee = $this->deliveryFee;
            $order->salesTax = $this->salesTax;
            $order->referralReduction = $this->referralReduction;
            $order->promotionReduction = $this->promotionReduction;
            $order->pointsReduction = $this->pointsReduction;
            $order->gratuity = $this->gratuity;
            $order->coolerDeposit = $this->coolerDeposit;
            $order->amount = $this->amount;
            $order->save();

            // Replace order meals
            $mealOrders = MealOrder::where('order_id', $order->id)->get();

            // Remove old meal orders & variations
            foreach ($mealOrders as $mealOrder) {
                foreach ($mealOrder->components as $component) {
                    $component->delete();
                }
                foreach ($mealOrder->addons as $addon) {
                    $addon->delete();
                }
                $mealOrder->delete();
            }

            // Remove old meal packages
            $mealPackageOrders = MealPackageOrder::where(
                'order_id',
                $order->id
            )->get();
            foreach ($mealPackageOrders as $mealPackageOrder) {
                $mealPackageOrder->delete();
            }

            // Add new meal orders & variations
            foreach ($bag->getItems() as $item) {
                $mealOrder = new MealOrder();
                $mealOrder->order_id = $order->id;
                $mealOrder->store_id = $this->store->id;
                $mealOrder->meal_id = $item['meal']['id'];
                $mealOrder->meal_size_id = $item['meal_size']['id'];
                $mealOrder->price = $item['price'];
                $mealOrder->quantity = $item['quantity'];
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
                $mealOrder->save();

                if (isset($item['components']) && $item['components']) {
                    foreach ($item['components'] as $component) {
                        try {
                            MealOrderComponent::create([
                                'meal_order_id' => $mealOrder->id,
                                'meal_component_id' => $component->id,
                                'meal_component_option_id' =>
                                    $component->option->id
                            ]);
                        } catch (\Exception $e) {
                        }
                    }
                }

                if (isset($item['addons']) && $item['addons']) {
                    foreach ($item['addons'] as $addon) {
                        MealOrderAddon::create([
                            'meal_order_id' => $mealOrder->id,
                            'meal_addon_id' => $addon->addon->id
                        ]);
                    }
                }
            }

            // Add new meal orders that exist in meal package orders that didn't get processed in the bag above.
            foreach ($items as $item) {
                if ($item->meal_package) {
                    $mealOrder = new MealOrder();
                    $mealOrder->order_id = $order->id;
                    $mealOrder->store_id = $this->store->id;
                    $mealOrder->meal_id = $item->meal_id;
                    $mealOrder->meal_size_id = $item->meal_size_id;
                    // $mealOrder->price = $item->price;
                    $mealOrder->quantity = $item->quantity;
                    $mealOrder->meal_package = 1;

                    if ($item->meal_package_subscription_id !== null) {
                        $mealPackageSub = MeaLPackageSubscription::where(
                            'id',
                            $item->meal_package_subscription_id
                        )->first();
                        $mealOrder->meal_package_order_id = MealPackageOrder::where(
                            [
                                'meal_package_id' =>
                                    $mealPackageSub->meal_package_id,
                                'meal_package_size_id' =>
                                    $mealPackageSub->meal_package_size_id,
                                'order_id' => $order->id
                            ]
                        )
                            ->pluck('id')
                            ->first();
                    }

                    $mealOrder->save();
                }
            }
        }
    }

    /**
     *  Handle payment failure
     *
     * @return boolean
     */
    public function paymentFailed(
        Collection $stripeInvoice,
        Collection $stripeEvent
    ) {
        $latestOrder = $this->getLatestUnpaidOrder(false);

        if (!$latestOrder) {
            throw new \Exception(
                'No unpaid order for subscription #' . $this->id
            );
        }

        $latestOrder->events()->create([
            'type' => 'payment_failed',
            'stripe_event' => $stripeEvent
        ]);

        if ($stripeInvoice->get('status') === 'void') {
            $latestOrder->delivery_date->addWeeks(1);
            $latestOrder->save();
        }

        try {
            $this->cancel();
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to cancel Subscription'
                ],
                500
            );
        }
    }

    /**
     * Cancel the subscription
     *
     * @return boolean
     */
    public function cancel()
    {
        try {
            $subscription = \Stripe\Subscription::retrieve(
                'sub_' . $this->stripe_id,
                [
                    'stripe_account' => $this->store->settings->stripe_id
                ]
            );

            // $subscription->cancel_at_period_end = true;
            $subscription->cancel();
        } catch (\Exception $e) {
            throw new \Exception(
                'Failed to cancel Subscription in Stripe - ' . $this->stripe_id
            );
        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now('utc')
        ]);

        if ($this->store->notificationEnabled('cancelled_subscription')) {
            $this->store->sendNotification('cancelled_subscription', [
                'subscription' => $this,
                'customer' => $this->customer
            ]);
        }

        $this->user->sendNotification('subscription_cancelled', [
            'subscription' => $this,
            'store' => $this->store,
            'customer' => $this->customer
        ]);
    }

    /**
     * Pause the subscription
     *
     * @return boolean
     */
    public function pause($withStripe = true)
    {
        if ($withStripe) {
            $subscription = \Stripe\Subscription::update(
                'sub_' . $this->stripe_id,
                [
                    'pause_collection' => [
                        'behavior' => 'void'
                    ]
                ],
                [
                    'stripe_account' => $this->store->settings->stripe_id
                ]
            );

            // Old method of pausing before Stripe released the pause feature

            // try {
            //     $coupon = \Stripe\Coupon::retrieve('subscription-paused', [
            //         'stripe_account' => $this->store->settings->stripe_id
            //     ]);
            // } catch (\Exception $e) {
            //     $coupon = \Stripe\Coupon::create(
            //         [
            //             'duration' => 'forever',
            //             'id' => 'subscription-paused',
            //             'percent_off' => 100
            //         ],
            //         [
            //             'stripe_account' => $this->store->settings->stripe_id
            //         ]
            //     );
            // }

            // $subscription = \Stripe\Subscription::retrieve(
            //     'sub_' . $this->stripe_id,
            //     [
            //         'stripe_account' => $this->store->settings->stripe_id
            //     ]
            // );
            // $subscription->coupon = 'subscription-paused';
            // $subscription->save();
        }

        $this->update([
            'status' => 'paused',
            'paused_at' => Carbon::now('utc')
        ]);

        $this->store->clearCaches();

        // if ($this->store->notificationEnabled('paused_subscription')) {
        //     $this->store->sendNotification('paused_subscription', $this);
        // }
    }

    /**
     * Resume the subscription
     *
     * @return boolean
     */
    public function resume($withStripe = true)
    {
        if ($withStripe) {
            $subscription = \Stripe\Subscription::update(
                'sub_' . $this->stripe_id,
                [
                    'pause_collection' => ''
                ],
                [
                    'stripe_account' => $this->store->settings->stripe_id
                ]
            );

            // Old method of resuming before Stripe released the pause feature

            // $subscription = \Stripe\Subscription::retrieve(
            //     'sub_' . $this->stripe_id,
            //     [
            //         'stripe_account' => $this->store->settings->stripe_id
            //     ]
            // );
            // $subscription->coupon = null;
            // $subscription->save();
        }

        $this->update([
            'status' => 'active',
            'paused_at' => null
        ]);

        $this->store->clearCaches();

        // if ($this->store->notificationEnabled('resumed_subscription')) {
        //     $this->store->sendNotification('resumed_subscription', $this);
        // }
    }

    public function apply100offCoupon()
    {
        try {
            $coupon = \Stripe\Coupon::retrieve('subscription-paused', [
                'stripe_account' => $this->store->settings->stripe_id
            ]);
        } catch (\Exception $e) {
            $coupon = \Stripe\Coupon::create(
                [
                    'duration' => 'forever',
                    'id' => 'subscription-paused',
                    'percent_off' => 100
                ],
                [
                    'stripe_account' => $this->store->settings->stripe_id
                ]
            );
        }

        $subscription = \Stripe\Subscription::retrieve(
            'sub_' . $this->stripe_id,
            [
                'stripe_account' => $this->store->settings->stripe_id
            ]
        );
        $subscription->coupon = 'subscription-paused';
        $subscription->save();
    }

    public function remove100offCoupon()
    {
        $subscription = \Stripe\Subscription::retrieve(
            'sub_' . $this->stripe_id,
            [
                'stripe_account' => $this->store->settings->stripe_id
            ]
        );
        $subscription->coupon = null;
        $subscription->save();

        $this->store->clearCaches();
    }

    public function getPaidOrderCountAttribute()
    {
        return $this->orders->where('paid', 1)->count();
    }

    public function getTotalItemQuantityAttribute()
    {
        $total = 0;

        foreach ($this->meal_subscriptions as $mealSub) {
            $total += $mealSub->quantity;
        }

        return $total;
    }
}
