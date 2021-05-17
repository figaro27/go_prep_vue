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
use App\Error;
use App\PickupLocation;
use App\User;

class Subscription extends Model
{
    protected $preReductionTotal = 0;

    protected $hidden = ['orders'];

    protected $fillable = [
        'status',
        'cancelled_at',
        'renewalCount',
        'next_renewal_at',
        'prepaid',
        'prepaidWeeks'
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
        'adjustedRenewalUTC',
        // 'total_item_quantity',
        'prepaidChargeWeek'
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
        'prepaid' => 'boolean',
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
        if ($this->intervalCount == 1 && !$this->prepaid) {
            return 'Weekly';
        }
        if ($this->prepaid) {
            return 'Weekly Prepaid - (Charged every ' .
                $this->prepaidWeeks .
                ' weeks)';
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

            $applyCharge =
                !$this->cashOrder &&
                $this->status != 'paused' &&
                $this->stripe_customer_id !== 'CASH' &&
                $this->amount > 0.5
                    ? true
                    : false;

            $markAsPaidOnly = false;
            $showPrepaidOrderAmounts = false;
            if (!$this->prepaidChargeWeek) {
                $markAsPaidOnly = true;
                $applyCharge = false;
                $showPrepaidOrderAmounts = true;
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
            $latestOrder->prepaid = $this->prepaid;
            $latestOrder->save();

            if ($charge) {
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
            }

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
            if (
                Order::where('order_number', $newOrder->order_number)->count() >
                0
            ) {
                $newOrder->order_number = $newOrder->order_number . 1;
            }
            $newOrder->prepaid = $this->prepaid;
            $newOrder->preFeePreDiscount = $this->prepaidChargeWeek
                ? $this->preFeePreDiscount
                : 0;
            $newOrder->mealPlanDiscount = $this->prepaidChargeWeek
                ? $this->mealPlanDiscount
                : 0;
            $newOrder->afterDiscountBeforeFees = $this->prepaidChargeWeek
                ? $this->afterDiscountBeforeFees
                : 0;
            $newOrder->deliveryFee = $this->prepaidChargeWeek
                ? $this->deliveryFee
                : 0;
            $newOrder->gratuity = $this->prepaidChargeWeek
                ? $this->gratuity
                : 0;
            $newOrder->coolerDeposit = $this->prepaidChargeWeek
                ? $this->coolerDeposit
                : 0;
            $newOrder->processingFee = $this->prepaidChargeWeek
                ? $this->processingFee
                : 0;
            $newOrder->salesTax = $this->prepaidChargeWeek
                ? $this->salesTax
                : 0;
            $newOrder->coupon_id = $this->prepaidChargeWeek
                ? $this->coupon_id
                : 0;
            $newOrder->couponReduction = $this->prepaidChargeWeek
                ? $this->couponReduction
                : 0;
            $newOrder->couponCode = $this->prepaidChargeWeek
                ? $this->couponCode
                : 0;
            $newOrder->referralReduction = $this->prepaidChargeWeek
                ? $this->referralReduction
                : 0;
            $newOrder->purchased_gift_card_id = $this->prepaidChargeWeek
                ? $this->purchased_gift_card_id
                : 0;
            $newOrder->purchasedGiftCardReduction = $this->prepaidChargeWeek
                ? $this->purchasedGiftCardReduction
                : 0;
            $newOrder->promotionReduction = $this->prepaidChargeWeek
                ? $this->promotionReduction
                : 0;
            $newOrder->pointsReduction = $this->prepaidChargeWeek
                ? $this->pointsReduction
                : 0;
            $newOrder->originalAmount = $this->prepaidChargeWeek
                ? $this->amount
                : 0;
            $newOrder->amount = $this->prepaidChargeWeek ? $this->amount : 0;

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
            $newOrder->notes = $this->notes;
            $newOrder->publicNotes = $this->publicNotes;

            // New order fields
            $newOrder->staff_member = $latestOrder->staff_member;
            $newOrder->pickup_location_name = PickupLocation::where(
                'id',
                $this->pickup_location_id
            )
                ->pluck('name')
                ->first();
            $newOrder->purchased_gift_card_code = $this->prepaidChargeWeek
                ? PurchasedGiftCard::where('id', $this->purchased_gift_card_id)
                    ->pluck('code')
                    ->first()
                : null;
            $newOrder->store_name = $latestOrder->store_name;
            $newOrder->transfer_type = ($this->shipping
                    ? 'Shipping'
                    : $this->pickup)
                ? 'Pickup'
                : 'Delivery';
            $newOrder->customer_name = $latestOrder->customer_name;
            $newOrder->customer_firstname = $latestOrder->firstname;
            $newOrder->customer_lastname = $latestOrder->lastname;
            $newOrder->customer_email = $latestOrder->customer_email;
            $newOrder->customer_address = $latestOrder->customer_address;
            $newOrder->customer_zip = $latestOrder->customer_zip;
            $newOrder->customer_company = $latestOrder->customer_company;
            $newOrder->customer_phone = $latestOrder->customer_phone;
            $newOrder->customer_city = $latestOrder->customer_city;
            $newOrder->customer_state = $latestOrder->customer_state;
            $newOrder->customer_delivery = $latestOrder->customer_delivery;
            $goPrepFee =
                $newOrder->afterDiscountBeforeFees *
                ($this->store->settings->application_fee / 100);
            $stripeFee =
                !$newOrder->cashOrder && $newOrder->amount > 0.5
                    ? $newOrder->amount * 0.029 + 0.3
                    : 0;
            $newOrder->goprep_fee = $goPrepFee;
            $newOrder->stripe_fee = $stripeFee;
            $newOrder->grandTotal = $newOrder->amount - $goPrepFee - $stripeFee;

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

            // Update? Not the best way of doing this. Fixes the latest order on prepaid subscriptions always being 1 behind in terms of showing the amount VS 0.
            if ($this->prepaid) {
                if ($isMultipleDelivery == 1) {
                    $latestOrder = $this->getLatestUnpaidMDOrder();
                } else {
                    $latestOrder = $this->getLatestUnpaidOrder();
                }
            }

            if ($applyCharge || $markAsPaidOnly) {
                $this->paid_order_count += 1;
                $customer = Customer::where([
                    'user_id' => $this->user_id,
                    'store_id' => $this->store_id
                ])->first();
                $customer->last_order = Carbon::now();
                $customer->total_payments += 1;
                $customer->total_paid += $this->amount;
                $customer->update();

                $userDetail = UserDetail::where(
                    'user_id',
                    $this->user_id
                )->first();
                $userDetail->total_payments += 1;
                if (!$userDetail->multiple_store_orders) {
                    $user = User::where('id', $this->user_id)->first();
                    foreach ($user->orders as $order) {
                        if ($order->store_id !== $this->store_id) {
                            $userDetail->multiple_store_orders = true;
                        }
                    }
                }
                $userDetail->update();
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
            // Cancelling the subscription for next month if cancelled_at is marked
            if (
                $this->prepaid &&
                $this->cancelled_at !== null &&
                $this->paid_order_count % $this->prepaidWeeks === 0
            ) {
                $this->status = 'cancelled';
                $this->save();
                return;
            }

            $this->next_renewal_at = $this->next_renewal_at
                ->addWeeks($this->intervalCount)
                ->minute(0)
                ->second(0);
            $this->failed_renewal = null;
            $this->failed_renewal_error = null;
            $this->save();

            // Fetching latest order again after updating figures
            // if ($isMultipleDelivery == 1) {
            //     $latestOrder = $this->getLatestUnpaidMDOrder();
            // } else {
            //     $latestOrder = $this->getLatestUnpaidOrder();
            // }

            $latestOrder->events()->create([
                'type' => 'payment_succeeded',
                'stripe_event' => !$applyCharge ? json_encode($charge) : null
            ]);

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
                    $this->user->email = $latestOrder->customer_email;
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
                                $this->syncPrices(false, true);
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
                                $this->syncPrices(false, true);
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
            $this->failed_renewal_error = $e->getMessage();
            $this->save();

            $error = new Error();
            $error->store_id = $this->store_id;
            $error->user_id = $this->user_id;
            $error->type = 'Renewal';
            $error->error = $e->getMessage() . ' (Subscription Renewal)';
            $error->save();
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
            $additionalFee =
                $this->store->details->country === 'US'
                    ? floor($this->amount * 0.4)
                    : 0;
            $charge = \Stripe\Charge::create(
                [
                    "amount" => round($this->amount * 100),
                    "currency" => $this->store->settings->currency,
                    "source" => $storeSource,
                    "application_fee" =>
                        round(
                            $this->afterDiscountBeforeFees *
                                $this->store->settings->application_fee
                        ) + $additionalFee,
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
    public function syncPrices($mealsReplaced = false, $syncPricesOnly = false)
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

        if (!$syncPricesOnly) {
            $this->syncDiscountPrices($mealsReplaced);
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

        if ($this->prepaid) {
            $total = $total * $this->prepaidWeeks;
        }

        $this->amount = $total;
        $this->save();

        if ($mealsReplaced) {
            $this->mealsReplaced = 1;
        }
        $this->save();

        // Now update all the future orders with the correct pricing
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

            // Removing to try to fix issue with longer than 7 day cutoff times. Will bring back if it causes issues.
            // if ($order->getCutoffDate()->isPast()) {
            //     continue;
            // }

            // Update order pricing
            $order->preFeePreDiscount = $this->prepaidChargeWeek
                ? $this->preFeePreDiscount
                : 0;
            $order->mealPlanDiscount = $this->prepaidChargeWeek
                ? $this->mealPlanDiscount
                : 0;
            $order->afterDiscountBeforeFees = $this->prepaidChargeWeek
                ? $this->afterDiscountBeforeFees
                : 0;
            $order->processingFee = $this->prepaidChargeWeek
                ? $this->processingFee
                : 0;
            $order->deliveryFee = $this->prepaidChargeWeek
                ? $this->deliveryFee
                : 0;
            $order->salesTax = $this->prepaidChargeWeek ? $this->salesTax : 0;
            $order->referralReduction = $this->prepaidChargeWeek
                ? $this->referralReduction
                : 0;
            $order->promotionReduction = $this->prepaidChargeWeek
                ? $this->promotionReduction
                : 0;
            $order->pointsReduction = $this->prepaidChargeWeek
                ? $this->pointsReduction
                : 0;
            $order->gratuity = $this->prepaidChargeWeek ? $this->gratuity : 0;
            $order->coolerDeposit = $this->prepaidChargeWeek
                ? $this->coolerDeposit
                : 0;
            $order->coupon_id = $this->prepaidChargeWeek ? $this->coupon_id : 0;
            $order->couponReduction = $this->prepaidChargeWeek
                ? $this->couponReduction
                : 0;
            $order->couponCode = $this->prepaidChargeWeek
                ? $this->couponCode
                : 0;
            $order->purchased_gift_card_id = $this->prepaidChargeWeek
                ? $this->purchased_gift_card_id
                : 0;
            $order->purchasedGiftCardReduction = $this->prepaidChargeWeek
                ? $this->purchasedGiftCardReduction
                : 0;
            $order->originalAmount = $this->prepaidChargeWeek
                ? $this->amount
                : 0;
            $order->amount = $this->prepaidChargeWeek ? $this->amount : 0;
            $modules = StoreModule::where('store_id', $this->store_id)->first();
            $order->balance =
                $this->cashOrder && !$modules->cashOrderNoBalance
                    ? $this->amount
                    : 0;
            $order->balance = $this->cashOrder ? $this->amount : null;
            $order->notes = $this->notes;
            $order->publicNotes = $this->publicNotes;
            $order->prepaid = $this->prepaid;
            $goPrepFee =
                $this->afterDiscountBeforeFees *
                ($this->store->settings->application_fee / 100);
            $stripeFee =
                !$this->cashOrder && $this->amount > 0.5
                    ? $this->amount * 0.029 + 0.3
                    : 0;
            $order->goprep_fee = $goPrepFee;
            $order->stripe_fee = $stripeFee;
            $order->grandTotal = $this->amount - $goPrepFee - $stripeFee;
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
        if (!$this->prepaid) {
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

    public function getPrepaidChargeWeekAttribute()
    {
        if (!$this->prepaid) {
            return true;
        }
        if (
            $this->paid_order_count === 0 ||
            $this->paid_order_count % $this->prepaidWeeks === 0
        ) {
            return true;
        } else {
            return false;
        }
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
