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
use App\MealAddon;
use App\MealComponent;
use App\MealComponentOption;
use App\MealSubscriptionComponent;
use App\MealSubscriptionAddon;
use App\PurchasedGiftCard;
use Illuminate\Support\Facades\Mail;
use App\Mail\Customer\RenewalFailed;
use App\Customer;
use App\StoreModule;

class Subscription extends Model
{
    protected $preReductionTotal = 0;

    protected $hidden = ['orders'];

    protected $fillable = [
        'status',
        'cancelled_at',
        'renewalCount',
        'next_renewal_at'
    ];

    protected $appends = [
        'store_name',
        // 'latest_order',
        // 'latest_paid_order',
        // 'latest_unpaid_order',
        // 'next_unpaid_delivery_date',
        // 'next_order',
        // 'meal_ids',
        // 'meal_quantities',
        // 'pre_coupon',
        'items',
        'meal_package_items',
        'interval_title',
        'transfer_type',
        'adjustedRenewal',
        'adjustedRenewalUTC'
        // 'total_item_quantity'
    ];

    protected $casts = [
        'latest_unpaid_order_date' => 'datetime',
        'next_delivery_date' => 'datetime',
        'next_renewal_at' => 'datetime',
        'preFeePreDiscount' => 'float',
        'afterDiscountBeforeFees' => 'float',
        'processingFee' => 'float',
        'deliveryFee' => 'float',
        'amount' => 'float',
        'salesTax' => 'float',
        'mealPlanDiscount' => 'float',
        'monthlyPrepay' => 'boolean',
        'mealsReplaced' => 'boolean',
        'gratuity' => 'float',
        'shipping' => 'boolean',
        'manual' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function card()
    {
        return $this->belongsTo('App\Card');
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

    public function getAdjustedRenewalAttribute()
    {
        $date = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $this->next_renewal_at,
            'UTC'
        );
        $date->setTimezone($this->store->settings->timezone);
        return $date;
    }

    public function getAdjustedRenewalUTCAttribute()
    {
        $date = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $this->next_renewal_at,
            $this->store->settings->timezone
        );
        $date->setTimezone('UTC');
        return $date;
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

    public function getTransferTypeAttribute()
    {
        if ($this->shipping) {
            return 'Shipping';
        } else {
            return $this->pickup ? 'Pickup' : 'Delivery';
        }
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

    public function getLatestUnpaidOrder()
    {
        return $this->orders()
            ->where('paid', 0)
            ->latest()
            ->first();
    }

    // public function getLatestUnpaidOrderAttribute()
    // {
    //     return $this->orders()
    //         ->where('paid', 0)
    //         ->latest()
    //         ->first();
    // }

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

    public function getNextUnpaidDeliveryDateAttribute()
    {
        if ($this->latest_unpaid_order) {
            $date = new Carbon(
                $this->latest_unpaid_order->delivery_date->toDateTimeString()
            );
            return $date;
        }
    }

    // public function getNextOrderAttribute()
    // {
    //     if (
    //         $this->latest_paid_order &&
    //         $this->latest_paid_order->delivery_date > Carbon::now()
    //     ) {
    //         return $this->latest_paid_order;
    //     } else {
    //         return $this->latest_unpaid_order;
    //     }
    // }

    public function getIntervalTitleAttribute()
    {
        if ($this->intervalCount == 1 && !$this->monthlyPrepay) {
            return 'Weekly';
        }
        if ($this->intervalCount == 1 && $this->monthlyPrepay) {
            return 'Weekly (Charged Monthly)';
        }
        if ($this->intervalCount == 2) {
            return 'Bi-Weekly';
        }
        if ($this->intervalCount == 4) {
            return 'Monthly';
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
                    'item_id' => $mealSub->id,
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
                    'added_price' => $mealSub->added_price
                        ? $mealSub->added_price
                        : 0,
                    'free' => $mealSub->free,
                    'special_instructions' => $mealSub->special_instructions,
                    'meal_package_subscription_id' =>
                        $mealSub->meal_package_subscription_id,
                    'category_id' => $mealSub->category_id,
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
    public function renew($manualRenewal = false)
    {
        try {
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

            $this->syncPrices();

            $applyCharge =
                !$this->cashOrder &&
                $this->status != 'paused' &&
                $this->stripe_customer_id !== 'CASH' &&
                $this->amount > 0.5
                    ? true
                    : false;

            // Cancelling the subscription for next month if cancelled_at is marked
            if (
                $this->monthlyPrepay &&
                $this->cancelled_at !== null &&
                $this->renewalCount % 4 === 0
            ) {
                $this->status = 'cancelled';
                $this->save();
                return;
            }

            // Only charge once per month on monthly prepay subscriptions
            $markAsPaidOnly = false;
            if ($this->monthlyPrepay) {
                if (
                    $this->renewalCount !== 0 &&
                    $this->renewalCount % 4 !== 0
                ) {
                    $markAsPaidOnly = true;
                }
            }
            if (
                ($this->cashOrder && $this->status !== 'paused') ||
                (!$this->cashOrder &&
                    $this->status !== 'paused' &&
                    $this->amount < 0.5)
            ) {
                $markAsPaidOnly = true;
            }

            // Charge
            $charge = $applyCharge ? $this->renewalCharge() : null;

            $latestOrder->paid = $applyCharge || $markAsPaidOnly ? 1 : 0;
            $latestOrder->paid_at =
                $applyCharge || $markAsPaidOnly ? new Carbon() : null;
            $latestOrder->stripe_id = $charge ? $charge->id : null;
            $latestOrder->save();

            $latestOrder->events()->create([
                'type' => 'payment_succeeded',
                'stripe_event' => !$applyCharge ? json_encode($charge) : null
            ]);

            $order_transaction = new OrderTransaction();
            $order_transaction->order_id = $latestOrder->id;
            $order_transaction->store_id = $latestOrder->store_id;
            $order_transaction->user_id = $latestOrder->user_id;
            $order_transaction->customer_id = $latestOrder->customer_id;
            $order_transaction->type = 'order';
            $order_transaction->stripe_id = $latestOrder->stripe_id
                ? $latestOrder->stripe_id
                : null;
            $order_transaction->card_id = $latestOrder->card_id
                ? $latestOrder->card_id
                : null;
            $order_transaction->amount = $latestOrder->amount;
            $order_transaction->save();

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
            $newOrder->purchased_gift_card_id = $this->purchased_gift_card_id;
            $newOrder->purchasedGiftCardReduction =
                $this->purchasedGiftCardReduction;
            $newOrder->promotionReduction = $this->promotionReduction;
            $newOrder->pointsReduction = $this->pointsReduction;
            $newOrder->originalAmount = $this->amount;
            $newOrder->amount = $this->amount;

            $modules = StoreModule::where('store_id', $this->store_id)->first();
            $newOrder->balance =
                $this->cashOrder && !$modules->cashOrderNoBalance
                    ? $this->amount
                    : 0;

            $newOrder->currency = $this->currency;
            $newOrder->fulfilled = false;
            $newOrder->pickup = $this->pickup;
            $newOrder->shipping = $this->shipping;
            $newOrder->cashOrder = $this->cashOrder;

            $newOrder->pickup_location_id = $this->pickup_location_id;
            $newOrder->transferTime = $this->transferTime;

            // Refine this
            $newOrder->delivery_date = $latestOrder->delivery_date->addWeeks(
                $this->intervalCount
            );
            $newOrder->isMultipleDelivery = $this->isMultipleDelivery;
            $newOrder->publicNotes = $this->publicNotes;
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
                if (
                    $isMultipleDelivery == 1 &&
                    $mealPackageSub->delivery_date
                ) {
                    $mealPackageSub->delivery_date = $mealPackageSub->delivery_date->addWeeks(
                        $this->intervalCount
                    );
                    $mealPackageSub->save();
                    $mealPackageOrder->delivery_date =
                        $mealPackageSub->delivery_date;
                }
                $mealPackageOrder->mappingId = $mealPackageSub->mappingId;
                $mealPackageOrder->category_id = $mealPackageSub->category_id;
                $mealPackageOrder->items_quantity =
                    $mealPackageSub->items_quantity;
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
                $mealOrder->special_instructions =
                    $mealSub->special_instructions;
                $mealOrder->meal_package = $mealSub->meal_package
                    ? $mealSub->meal_package
                    : 0;
                $mealOrder->free = $mealSub->free ? $mealSub->free : 0;
                $mealOrder->customTitle = $mealSub->customTitle;
                $mealOrder->customSize = $mealSub->customSize;
                $mealOrder->category_id = $mealSub->category_id;

                // For purposes of showing added price on components & addons that cost extra. Eventually switch entirely to just having price and not be meal_package_variation
                if ($mealSub->price > 0 && $mealSub->meal_package) {
                    $mealOrder->meal_package_variation = 1;
                }

                if ($mealSub->meal_package_subscription_id !== null) {
                    $mealPackageSub = MealPackageSubscription::where(
                        'id',
                        $mealSub->meal_package_subscription_id
                    )->first();
                    $mealOrder->meal_package_order_id = MealPackageOrder::where(
                        [
                            'meal_package_id' =>
                                $mealPackageSub->meal_package_id,
                            'meal_package_size_id' =>
                                $mealPackageSub->meal_package_size_id,
                            'order_id' => $newOrder->id,
                            'mappingId' => $mealPackageSub->mappingId
                        ]
                    )
                        ->pluck('id')
                        ->first();
                }

                if ($isMultipleDelivery == 1 && $mealSub->delivery_date) {
                    $mealSub->delivery_date = $mealSub->delivery_date->addWeeks(
                        $this->intervalCount
                    );

                    $mealSub->save();
                    $mealOrder->delivery_date = $mealSub->delivery_date;
                }

                $mealOrder->added_price = $mealSub->added_price
                    ? $mealSub->added_price
                    : 0;

                $mealOrder->save();

                if ($mealSub->has('components')) {
                    foreach ($mealSub->components as $component) {
                        MealOrderComponent::create([
                            'meal_order_id' => $mealOrder->id,
                            'meal_component_id' =>
                                $component->meal_component_id,
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
            }

            if ($this->status !== 'paused') {
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

                // Updating item stock
                if ($this->store->modules->stockManagement) {
                    foreach ($this->meal_subscriptions as $mealSub) {
                        $meal = Meal::where('id', $mealSub->meal_id)->first();
                        if ($meal && $meal->stock !== null) {
                            if ($meal->stock === 0) {
                                $mealSub->delete();
                                $this->syncPrices();
                            } elseif ($meal->stock < $mealSub->quantity) {
                                $unitPrice =
                                    $mealSub->price / $mealSub->quantity;
                                $mealSub->quantity = $meal->stock;
                                $mealSub->price =
                                    $unitPrice * $mealSub->quantity;
                                $mealSub->update();
                                $meal->stock = 0;
                                $meal->lastOutOfStock = date('Y-m-d H:i:s');
                                $meal->active = 0;
                                $this->syncPrices();
                            } else {
                                $meal->stock -= $mealSub->quantity;
                                if ($meal->stock === 0) {
                                    $meal->lastOutOfStock = date('Y-m-d H:i:s');
                                    $meal->active = 0;
                                }
                            }
                            $meal->update();
                        }
                    }
                }
            }

            if ($applyCharge || $markAsPaidOnly) {
                $this->paid_order_count += 1;
                $customer = Customer::where('user_id', $this->user_id)->first();
                $customer->last_order = Carbon::now();
                $customer->total_payments += 1;
                $customer->total_paid += $this->amount;
                $customer->update();
            }

            $nextOrder = $this->orders->firstWhere(
                'delivery_date',
                '>=',
                Carbon::now()
            );
            $this->next_delivery_date = $nextOrder->delivery_date;

            $this->latest_unpaid_order_date = $this->orders()
                ->where('paid', 0)
                ->latest()
                ->pluck('delivery_date')
                ->first();

            $this->renewalCount += 1;
            $this->next_renewal_at = $this->next_renewal_at
                ->addWeeks($this->intervalCount)
                ->minute(0)
                ->second(0);
            $this->failed_renewal = null;
            $this->save();
        } catch (\Exception $e) {
            $id = $this->id;
            $sub = $this->replicate();
            $sub->id = $id;
            $sub->error = $e->getMessage();
            $sub->timestamp = Carbon::now('utc')->subHours('5');
            $email = new RenewalFailed($sub->toArray());

            if (
                strpos($sub->error, 'unpaid order') !== false ||
                strpos($sub->error, 'stripe_id') !== false ||
                strpos($sub->error, 'specify a customer') !== false
            ) {
                Mail::to('mike@goprep.com')->send($email);
            } else {
                if (!$manualRenewal) {
                    Mail::to($sub->user->email)
                        ->bcc($sub->store->user->email)
                        ->send($email);
                }
            }

            $this->failed_renewal = Carbon::now('UTC');
            $this->save();
            $this->failed_renewal_error = $e->getMessage();
        }
    }

    public function removeOneTimeCoupons()
    {
        $coupon = Coupon::where('id', $this->coupon_id)->first();
        if (isset($coupon) && $coupon->oneTime) {
            $this->coupon_id = null;
            $this->couponReduction = null;
            $this->couponCode = null;
            $this->update();
        }
    }

    public function syncDiscountPrices($mealsReplaced = false)
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
                        $this->preFeePreDiscount >= $promotion->conditionAmount
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
                        $this->total_item_quantity >=
                        $promotion->conditionAmount
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

        // Only update the customer points and purchased gift card balance if this is a subscription renewal, not a meal replacement.

        if (!$mealsReplaced) {
            // Maybe add option in the future on user checkout to continue applying accumulated points to subscription renewal or not
            if ($pointsRate > 0) {
                $customer = Customer::where('id', $this->customer_id)->first();
                $customer->points -= $this->pointsReduction * 100;
                $customer->points += $this->preFeePreDiscount * $pointsRate;
                $customer->update();
            }

            // Set points reduction on subscription to 0 after renewal since points were all used on first order
            $this->pointsReduction = 0;
            $this->update();

            // Update the purchased gift card amount

            if ($this->purchased_gift_card_id && $this->status !== 'paused') {
                $purchasedGiftCard = PurchasedGiftCard::where(
                    'id',
                    $this->purchased_gift_card_id
                )->first();
                $purchasedGiftCardBalance = $purchasedGiftCard->balance;
                if ($purchasedGiftCardBalance >= $this->preReductionTotal) {
                    $purchasedGiftCardBalance = $this->preReductionTotal;
                }

                $purchasedGiftCard->balance -= $purchasedGiftCardBalance;
                $purchasedGiftCard->update();
                $this->purchasedGiftCardReduction = $purchasedGiftCardBalance;
                $this->update();
            }
        } else {
            // Check referral type and set referral reduction to 0 if the frequency type is not all orders.
            if (
                $this->store->referralSettings &&
                $this->store->referralSettings->frequency !== 'allOrders'
            ) {
                $this->referralReduction = 0;
                $this->update();
            }
        }
    }

    public function renewalCharge()
    {
        try {
            $renewalCount = $this->renewalCount ? $this->renewalCount + 1 : 1;
            $storeSource = \Stripe\Source::create(
                [
                    "customer" => $this->user->stripe_id,
                    "original_source" => $this->card->stripe_id,
                    "usage" => "single_use"
                ],
                ["stripe_account" => $this->store->settings->stripe_id]
            );

            $charge = \Stripe\Charge::create(
                [
                    "amount" => round($this->amount * 100),
                    "currency" => $this->store->settings->currency,
                    "source" => $storeSource,
                    "application_fee" => round(
                        $this->afterDiscountBeforeFees *
                            $this->store->settings->application_fee
                    ),
                    'description' =>
                        'Renewal #' .
                        $renewalCount .
                        ' for Subscription ID: ' .
                        $this->stripe_id .
                        ' | Store: ' .
                        $this->store->details->name .
                        ' |  User ID: ' .
                        $this->user->id .
                        ' ' .
                        $this->user->details->full_name
                ],
                ["stripe_account" => $this->store->settings->stripe_id],
                [
                    "idempotency_key" =>
                        substr(uniqid(rand(10, 99), false), 0, 14) .
                        chr(rand(65, 90)) .
                        rand(0, 9)
                ]
            );

            return $charge;
        } catch (\Stripe\Error\Charge $e) {
            return response()->json(
                [
                    'error' => trim(
                        json_encode($e->jsonBody['error']['message']),
                        '"'
                    )
                ],
                400
            );
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
    public function syncPrices($mealsReplaced = false)
    {
        $items = $this->fresh()->meal_subscriptions->map(function ($meal) {
            if (!$meal->meal_package) {
                $price = $meal->price;
                return [
                    'quantity' => $meal->quantity,
                    'meal' => $meal->meal,
                    'price' => $price,
                    'delivery_date' => $meal->delivery_date,
                    'size' => $meal->meal_size_id,
                    'components' => $meal->components,
                    'addons' => $meal->addons
                ];
            }
        });

        $store = $this->store;

        $bag = new Bag($items, $store);

        $prePackagePrice = $bag->getTotalSync();

        $totalPackagePrice = 0;
        foreach (
            $this->fresh()->meal_package_subscriptions
            as $mealPackageSub
        ) {
            $totalPackagePrice +=
                $mealPackageSub->price * $mealPackageSub->quantity;
        }

        $total = $prePackagePrice + $totalPackagePrice;
        $afterDiscountBeforeFees = $prePackagePrice + $totalPackagePrice;
        $preFeePreDiscount = $prePackagePrice + $totalPackagePrice;

        // Adjust the price of the subscription on renewal if a one time coupon code was used. (Remove coupon from subscription).
        if ($this->renewalCount > 0) {
            $this->removeOneTimeCoupons();
        }
        $coupon = Coupon::where('id', $this->coupon_id)->first();
        if (isset($coupon)) {
            if (!$coupon->active) {
                $this->coupon_id = null;
                $this->couponReduction = null;
                $this->couponCode = null;
                $this->update();
            } else {
                if ($preFeePreDiscount >= $coupon->minimum) {
                    if ($coupon->type == 'percent') {
                        $couponReduction =
                            $preFeePreDiscount * ($coupon->amount / 100);
                        $this->couponReduction = $couponReduction;
                        $this->update();
                        $afterDiscountBeforeFees -= $couponReduction;
                        $total -= $couponReduction;
                    } else {
                        $afterDiscountBeforeFees -= $this->couponReduction;
                        $total -= $this->couponReduction;
                    }
                } else {
                    $this->coupon_id = null;
                    $this->couponReduction = null;
                    $this->couponCode = null;
                    $this->update();
                }
            }
        } else {
            $this->coupon_id = null;
            $this->couponReduction = null;
            $this->couponCode = null;
            $this->update();
        }

        $deliveryFee = $this->deliveryFee;
        $processingFee = 0;
        $mealPlanDiscount = 0;

        if ($this->store->settings->applyMealPlanDiscount) {
            $discount = $this->store->settings->mealPlanDiscount / 100;
            $mealPlanDiscount = $preFeePreDiscount * $discount;
            $total -= $mealPlanDiscount;
            $afterDiscountBeforeFees -= $mealPlanDiscount;
        }

        if ($this->afterDiscountBeforeFees > 0) {
            $salesTaxRate =
                round(
                    100 * ($this->salesTax / $this->afterDiscountBeforeFees),
                    2
                ) / 100;
        } else {
            $salesTaxRate = 0;
        }

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

        $salesTax = round($afterDiscountBeforeFees * $salesTaxRate, 2);
        $total += $salesTax;

        // Update subscription pricing
        $this->preFeePreDiscount = $preFeePreDiscount;
        $this->mealPlanDiscount = $mealPlanDiscount;
        if ($afterDiscountBeforeFees < 0) {
            $afterDiscountBeforeFees = 0;
        }
        $this->afterDiscountBeforeFees = $afterDiscountBeforeFees;
        $this->processingFee = $processingFee;
        $this->deliveryFee = $deliveryFee;
        $this->salesTax = $salesTax;

        $this->save();

        $this->preReductionTotal = $total;

        $this->updateCurrentMealOrders();

        $this->syncDiscountPrices($mealsReplaced);

        $total -= $this->referralReduction;
        $total -= $this->promotionReduction;
        $total -= $this->pointsReduction;
        $total -= $this->purchasedGiftCardReduction;
        $this->gratuity = $gratuity;
        $this->coolerDeposit = $coolerDeposit;
        if ($total < 0) {
            $total = 0;
        }
        // $this->amount = floor($total * 100) / 100;
        $this->amount = $total;
        $this->save();

        if ($mealsReplaced) {
            $this->mealsReplaced = 1;
        }
        $this->save();
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

            // Removing to try to fix issue with longer than 7 day cutoff times. Will bring back if it causes issues.
            // if ($order->cutoff_passed) {
            //     continue;
            // }

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
            $order->coupon_id = $this->coupon_id;
            $order->couponReduction = $this->couponReduction;
            $order->couponCode = $this->couponCode;
            $order->purchased_gift_card_id = $this->purchased_gift_card_id;
            $order->purchasedGiftCardReduction =
                $this->purchasedGiftCardReduction;
            $order->amount = $this->amount;
            $modules = StoreModule::where('store_id', $this->store_id)->first();
            $order->balance =
                $this->cashOrder && !$modules->cashOrderNoBalance
                    ? $this->amount
                    : 0;
            $order->balance = $this->cashOrder ? $this->amount : null;
            $order->publicNotes = $this->publicNotes;
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
                $mealOrder->added_price = isset($item['added_price'])
                    ? $item['added_price']
                    : 0;
                $mealOrder->quantity = $item['quantity'];
                if (isset($item['delivery_date']) && $item['delivery_date']) {
                    $mealOrder->delivery_date = $item['delivery_date'];
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
                    $mealOrder->meal_package_order_id =
                        $item['meal_package_subscription_id'];
                }

                if (isset($item['meal_package_title'])) {
                    $mealOrder->meal_package_title =
                        $item['meal_package_title'];
                }
                $mealOrder->added_price = isset($item['added_price'])
                    ? $item['added_price']
                    : 0;
                $mealOrder->save();

                if (isset($item['components']) && $item['components']) {
                    foreach ($item['components'] as $component) {
                        try {
                            MealOrderComponent::create([
                                'meal_order_id' => $mealOrder->id,
                                'meal_component_id' =>
                                    $component['meal_component_id'],
                                'meal_component_option_id' =>
                                    $component['meal_component_option_id']
                            ]);
                        } catch (\Exception $e) {
                        }
                    }
                }

                if (isset($item['addons']) && $item['addons']) {
                    foreach ($item['addons'] as $addon) {
                        try {
                            MealOrderAddon::create([
                                'meal_order_id' => $mealOrder->id,
                                'meal_addon_id' => $addon->addon->id
                            ]);
                        } catch (\Exception $e) {
                        }
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
                    $mealOrder->added_price = isset($item->added_price)
                        ? $item->added_price
                        : 0;
                    $mealOrder->quantity = $item->quantity;
                    $mealOrder->meal_package = 1;
                    $mealOrder->delivery_date = $item->delivery_date;

                    if ($item->meal_package_subscription_id !== null) {
                        $mealPackageSub = MealPackageSubscription::where(
                            'id',
                            $item->meal_package_subscription_id
                        )->first();

                        if (
                            MealPackageOrder::where([
                                'meal_package_id' =>
                                    $mealPackageSub->meal_package_id,
                                'meal_package_size_id' =>
                                    $mealPackageSub->meal_package_size_id,
                                'customTitle' => $mealPackageSub->customTitle,
                                'order_id' => $order->id,
                                'mappingId' => isset($mealPackageSub->mappingId)
                                    ? $mealPackageSub->mappingId
                                    : null
                            ])
                                ->get()
                                ->count() === 0
                        ) {
                            $mealPackageOrder = new MealPackageOrder();
                            $mealPackageOrder->store_id = $this->store->id;
                            $mealPackageOrder->order_id = $order->id;
                            $mealPackageOrder->meal_package_id =
                                $mealPackageSub->meal_package_id;
                            $mealPackageOrder->meal_package_size_id =
                                $mealPackageSub->meal_package_size_id;
                            $mealPackageOrder->quantity =
                                $mealPackageSub->quantity;
                            $mealPackageOrder->customTitle = isset(
                                $mealPackageSub->customTitle
                            )
                                ? $mealPackageSub->customTitle
                                : null;
                            $mealPackageOrder->price = $mealPackageSub->price;

                            if (
                                isset($mealPackageSub->delivery_date) &&
                                $mealPackageSub->delivery_date
                            ) {
                                $mealPackageOrder->delivery_date =
                                    $mealPackageSub->delivery_date;
                            }
                            $mealPackageOrder->mappingId = isset(
                                $mealPackageSub->mappingId
                            )
                                ? $mealPackageSub->mappingId
                                : null;
                            $mealPackageOrder->items_quantity =
                                $mealOrder->quantity;
                            $mealPackageOrder->save();

                            $mealOrder->meal_package_order_id =
                                $mealPackageOrder->id;
                        } else {
                            $mealOrder->meal_package_order_id = MealPackageOrder::where(
                                [
                                    'meal_package_id' =>
                                        $mealPackageSub->meal_package_id,
                                    'meal_package_size_id' =>
                                        $mealPackageSub->meal_package_size_id,
                                    'customTitle' =>
                                        $mealPackageSub->customTitle,
                                    'order_id' => $order->id,
                                    'mappingId' => isset(
                                        $mealPackageSub->mappingId
                                    )
                                        ? $mealPackageSub->mappingId
                                        : null
                                ]
                            )
                                ->pluck('id')
                                ->first();
                            $mealPackageOrder->items_quantity +=
                                $mealOrder->quantity;
                            $mealPackageOrder->update();
                        }
                        $mealOrder->added_price = isset($item['added_price'])
                            ? $item['added_price']
                            : 0;
                        $mealOrder->save();
                    }
                }
            }
        }
    }

    /**
     * Cancel the subscription
     *
     * @return boolean
     */
    public function cancel($failedRenewalExpired = false)
    {
        if (!$this->monthlyPrepay) {
            $this->status = 'cancelled';
        }
        $this->cancelled_at = Carbon::now('utc');
        $this->update();

        if ($this->store->notificationEnabled('cancelled_subscription')) {
            $this->store->sendNotification('cancelled_subscription', [
                'subscription' => $this,
                'customer' => $this->customer,
                'failedRenewalExpired' => $failedRenewalExpired
            ]);
        }

        $this->user->sendNotification('subscription_cancelled', [
            'subscription' => $this,
            'store' => $this->store,
            'customer' => $this->customer
        ]);

        // Adding stock back to the meal if the subscription is cancelled
        if ($this->store->modules->stockManagement) {
            foreach ($this->meal_subscriptions as $mealSub) {
                $meal = Meal::where('id', $mealSub->meal_id)->first();
                $meal->stock += $mealSub->quantity;
                $meal->update();
            }
        }
    }

    /**
     * Pause the subscription
     *
     * @return boolean
     */
    public function pause()
    {
        $this->update([
            'status' => 'paused',
            'paused_at' => Carbon::now('utc')
        ]);

        $this->store->clearCaches();
    }

    /**
     * Resume the subscription
     *
     * @return boolean
     */
    public function resume()
    {
        $nextRenewalAt = $this->next_renewal_at;

        if ($nextRenewalAt->isPast()) {
            $nextRenewalAt = Carbon::now()->addMinutes('30');
        }

        $this->update([
            'status' => 'active',
            'paused_at' => null,
            'next_renewal_at' => $nextRenewalAt
        ]);

        $this->store->clearCaches();
    }

    public function getTotalItemQuantityAttribute()
    {
        $total = 0;

        foreach ($this->meal_subscriptions as $mealSub) {
            $total += $mealSub->quantity;
        }

        return $total;
    }

    // Removes deleted variations from existing subscriptions and updates the pricing
    public static function removeVariations($type, $variations)
    {
        // if ($type == 'sizes'){
        //     foreach ($variations as $size){
        //         // Get the price of the regular meal (without the size)
        //         $mealPrice = Meal::where('id', $size->meal_id)->pluck('price')->first();
        //         $mealSizePrice = MealSize::where('id', $size->id)->pluck('price')->first();
        //         $price = $mealSizePrice - $mealPrice;
        //         // Adjust the pricing of all meal subscriptions
        //         $mealSubscriptions = MealSubscription::where('meal_size_id', $size->id)->get();

        //             foreach ($mealSubscriptions as $mealSubscription){
        //                 $mealSubscription->price -= $price * $mealSubscription->quantity;
        //                 $mealSubscription->meal_size_id = null;
        //                 $mealSubscription->save();
        //                 $subscriptions = Subscription::where('id', $mealSubscription->subscription_id)->where('status', '!=', 'cancelled')->get();
        //                 foreach ($subscriptions as $subscription){
        //                     $subscription->syncPrices();
        //                 }
        //         }
        //     }
        // }

        if ($type == 'componentOptions') {
            foreach ($variations as $componentOption) {
                // Get the price of the component option
                $price = MealComponentOption::where('id', $componentOption->id)
                    ->pluck('price')
                    ->first();
                // Adjust the pricing of all meal subscriptions
                $mealSubscriptionIds = MealSubscriptionComponent::where(
                    'meal_component_option_id',
                    $componentOption->id
                )
                    ->get()
                    ->map(function ($componentOption) {
                        return $componentOption->meal_subscription_id;
                    });

                MealSubscriptionComponent::where(
                    'meal_component_option_id',
                    $componentOption->id
                )->delete();

                foreach ($mealSubscriptionIds as $mealSubscriptionId) {
                    $mealSubscriptions = MealSubscription::where(
                        'id',
                        $mealSubscriptionId
                    )->get();
                    foreach ($mealSubscriptions as $mealSubscription) {
                        $mealSubscription->price -=
                            $price * $mealSubscription->quantity;
                        $mealSubscription->save();
                        $subscriptions = Subscription::where(
                            'id',
                            $mealSubscription->subscription_id
                        )
                            ->where('status', '!=', 'cancelled')
                            ->get();
                        foreach ($subscriptions as $subscription) {
                            $subscription->syncPrices();
                        }
                    }
                }
            }
        }

        if ($type == 'addons') {
            foreach ($variations as $addon) {
                // Get the price of the addon
                $price = MealAddon::where('id', $addon->id)
                    ->pluck('price')
                    ->first();
                // Adjust the pricing of all meal subscriptions
                $mealSubscriptionIds = MealSubscriptionAddon::where(
                    'meal_addon_id',
                    $addon->id
                )
                    ->get()
                    ->map(function ($addon) {
                        return $addon->meal_subscription_id;
                    });

                MealSubscriptionAddon::where(
                    'meal_addon_id',
                    $addon->id
                )->delete();

                foreach ($mealSubscriptionIds as $mealSubscriptionId) {
                    $mealSubscriptions = MealSubscription::where(
                        'id',
                        $mealSubscriptionId
                    )->get();
                    foreach ($mealSubscriptions as $mealSubscription) {
                        $mealSubscription->price -=
                            $price * $mealSubscription->quantity;
                        $mealSubscription->save();
                        $subscriptions = Subscription::where(
                            'id',
                            $mealSubscription->subscription_id
                        )
                            ->where('status', '!=', 'cancelled')
                            ->get();
                        foreach ($subscriptions as $subscription) {
                            $subscription->syncPrices();
                        }
                    }
                }
            }
        }
    }
}
