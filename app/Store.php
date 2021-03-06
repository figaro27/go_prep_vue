<?php

namespace App;

use App\Mail\Store\CancelledSubscription;
use App\Mail\Store\NewOrder;
use App\Mail\Store\NewSubscription;
use App\Mail\Store\ReadyToPrint;
use App\Mail\Store\AdjustedOrder;
use App\Mail\Customer\NewGiftCard;
use App\Mail\Store\SignupCheckin;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\StoreSetting;
use App\Order;

class Store extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['accepted_toa'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['orders', 'customers', 'plan'];

    protected $appends = [
        'cutoff_passed',
        'next_delivery_date',
        'next_cutoff_date',
        'next_locked_in_date',
        'url',
        'hasPromoCodes',
        'bulkCustomers',
        'hasDeliveryDayItems',
        'status',
        'active_child_store_ids'
    ];

    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            $model->clearCaches();
        });
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $arr = parent::toArray();
        return $arr;
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function parentStore()
    {
        return $this->belongsToMany(
            'App\Store',
            'child_parent_stores',
            'store_id',
            'parent_store_id'
        );
    }

    public function childStores()
    {
        return $this->belongsToMany(
            'App\Store',
            'child_parent_stores',
            'parent_store_id',
            'store_id'
        );
    }

    public function orders()
    {
        return $this->hasMany('App\Order')->orderBy('created_at', 'desc');
    }

    public function payments()
    {
        return $this->hasMany('App\Payment')->orderBy('created_at', 'desc');
    }

    public function payouts()
    {
        return $this->hasMany('App\Payout');
    }

    public function surveyQuestions()
    {
        return $this->hasMany('App\SurveyQuestion');
    }

    public function surveyResponses()
    {
        return $this->hasMany('App\SurveyResponse');
    }

    public function mealOrders()
    {
        return $this->hasMany('App\MealOrder');
    }

    public function subscriptions()
    {
        return $this->hasMany('App\Subscription')->orderBy(
            'created_at',
            'desc'
        );
    }

    public function meals()
    {
        return $this->hasMany('App\Meal')->orderBy('title');
    }

    public function childMeals()
    {
        return $this->belongsToMany(
            'App\Meal',
            'child_meals',
            'store_id',
            'meal_id'
        );
    }

    public function mealSizes()
    {
        return $this->hasMany('App\MealSize');
    }

    public function packages()
    {
        return $this->hasMany('App\MealPackage')->orderBy('title');
    }

    public function childPackages()
    {
        return $this->belongsToMany(
            'App\MealPackage',
            'child_meal_packages',
            'store_id',
            'meal_package_id'
        );
    }

    public function ingredients()
    {
        return $this->hasMany('App\Ingredient')->orderBy('food_name');
    }

    public function units()
    {
        return $this->hasMany('App\StoreUnit');
    }

    public function staff()
    {
        return $this->hasMany('App\Staff');
    }

    public function menuSessions()
    {
        return $this->hasMany('App\MenuSession');
    }

    public function storeDetail()
    {
        return $this->hasOne('App\StoreDetail');
    }

    public function menuSettings()
    {
        return $this->hasOne('App\MenuSetting');
    }

    public function details()
    {
        return $this->hasOne('App\StoreDetail');
    }

    public function settings()
    {
        return $this->hasOne('App\StoreSetting');
    }

    public function modules()
    {
        return $this->hasOne('App\StoreModule');
    }

    public function moduleSettings()
    {
        return $this->hasOne('App\StoreModuleSettings');
    }

    public function reportSettings()
    {
        return $this->hasOne('App\ReportSetting');
    }

    public function labelSettings()
    {
        return $this->hasOne('App\LabelSetting');
    }

    public function orderLabelSettings()
    {
        return $this->hasOne('App\OrderLabelSetting');
    }

    public function smsSettings()
    {
        return $this->hasOne('App\SmsSetting');
    }

    public function reportRecords()
    {
        return $this->hasOne('App\ReportRecord');
    }

    public function categories()
    {
        return $this->hasMany('App\Category')->orderBy('order');
    }

    public function customers()
    {
        return $this->hasMany('App\Customer');
    }

    public function coupons()
    {
        return $this->hasMany('App\Coupon');
    }

    public function giftCards()
    {
        return $this->hasMany('App\GiftCard');
    }

    public function childGiftCards()
    {
        return $this->belongsToMany(
            'App\GiftCard',
            'child_gift_cards',
            'store_id',
            'gift_card_id'
        );
    }

    public function purchasedGiftCards()
    {
        return $this->hasMany('App\PurchasedGiftCard');
    }

    public function pickupLocations()
    {
        return $this->hasMany('App\PickupLocation');
    }

    public function productionGroups()
    {
        return $this->hasMany('App\ProductionGroup');
    }

    public function lineItems()
    {
        return $this->hasMany('App\LineItem');
    }

    public function deliveryFeeRanges()
    {
        return $this->hasMany('App\DeliveryFeeRange');
    }

    public function deliveryFeeZipCodes()
    {
        return $this->hasMany('App\DeliveryFeeZipCode');
    }

    public function deliveryDayZipCodes()
    {
        return $this->hasMany('App\DeliveryDayZipCode');
    }

    public function holidayTransferTimes()
    {
        return $this->hasMany('App\HolidayTransferTime');
    }

    public function plan()
    {
        return $this->hasOne('App\StorePlan');
    }

    public function deliveryDays()
    {
        return $this->hasMany('App\DeliveryDay');
    }

    public function deliveryDayMeals()
    {
        return $this->hasMany('App\DeliveryDayMeal');
    }

    public function deliveryDayMealPackages()
    {
        return $this->hasMany('App\DeliveryDayMealPackage');
    }

    public function referrals()
    {
        return $this->hasMany('App\Referral')->with('user');
    }

    public function errors()
    {
        return $this->hasMany('App\Error')->with('user');
    }

    public function referralSettings()
    {
        return $this->hasOne('App\ReferralSetting');
    }

    public function packingSlipSettings()
    {
        return $this->hasOne('App\PackingSlipSetting');
    }

    public function promotions()
    {
        return $this->hasMany('App\Promotion');
    }

    public function getDeliveryDayByWeekIndex($weekIndex)
    {
        return $this->deliveryDays->firstWhere('day', $weekIndex);
    }

    public function clearCaches()
    {
        Cache::forget('store_order_ingredients' . $this->id);
        Cache::forget('store_logo_' . $this->id);
        Cache::forget('store_' . $this->id . '_subscribed_delivery_days');
        //Cache::forget('store_' . $this->id . '_ddbwi');
    }

    public function getUrl($append = '', $secure = true)
    {
        $protocol = config('app.secure') && $secure ? 'https://' : 'http://';
        if (starts_with($append, config('app.url'))) {
            $append = str_replace(config('app.url'), '', $append);
        }
        if (!$this->details->host) {
            $url =
                $protocol .
                $this->details->domain .
                '.' .
                config('app.domain') .
                $append;
        } else {
            $append = str_replace('https//goprep.com', '', $append);
            $url = $append;
        }

        return $url;
    }

    public function getConnectUrl()
    {
        if (env('APP_ENV') === 'production') {
            $ca = 'ca_ER2OUNQq30X2xHMqkWo8ilUSz7Txyn1A';
        } else {
            $ca = 'ca_ER2OYlaTUrWz7LRQvhtKLIjZsRcM8mh9';
        }

        if (in_array($this->details->country, ['US', 'CA', 'FR'])) {
            return "https://connect.stripe.com/express/oauth/authorize?client_id=$ca";
        } else {
            return "https://connect.stripe.com/oauth/authorize?client_id=$ca&response_type=code&scope=read_write";
        }
    }

    public static function getStore($id)
    {
        return Store::with('storeDetail', 'order')
            ->where('id', $id)
            ->first();
    }

    public static function getStores()
    {
        return Store::with('storeDetail', 'order')
            ->get()
            ->map(function ($store) {
                return [
                    "id" => $store->id,
                    "logo" => $store->storeDetail->logo,
                    "name" => $store->storeDetail->name,
                    "phone" => $store->storeDetail->phone,
                    "address" => $store->storeDetail->address,
                    "city" => $store->storeDetail->city,
                    "state" => $store->storeDetail->state,
                    "Joined" => $store->created_at->format('m-d-Y'),
                    "TotalOrders" => $store->order->count(),
                    "TotalCustomers" => Order::all()
                        ->unique('user_id')
                        ->where('store_id', '=', $store->id)
                        ->count(),
                    "TotalPaid" =>
                        '$' .
                        number_format(
                            Order::all()
                                ->where('store_id', '=', $store->id)
                                ->pluck('amount')
                                ->sum(),
                            2,
                            '.',
                            ','
                        )
                ];
            });
    }

    public function getOrderIngredients($dateRange = [])
    {
        // $excludeFulfilled = true
        $ingredients = [];

        $orders = $this->orders()
            ->with(['meals', 'meals.ingredients', 'meal_orders'])
            ->where(['paid' => 1, 'voided' => 0]);

        if ($dateRange === []) {
            //$orders = $orders->where('delivery_date', $this->getNextDeliveryDate());
        }

        // Disabled Old Workflow
        /*
        if (isset($dateRange['from'])) {
            $from = Carbon::parse($dateRange['from']);
            $orders = $orders->where(
                'delivery_date',
                '>=',
                $from->format('Y-m-d')
            );
        } else {
            $orders = $orders->where(
                'delivery_date',
                '>=',
                Carbon::now()->format('Y-m-d')
            );
        }
        if (isset($dateRange['to'])) {
            $to = Carbon::parse($dateRange['to']);
            $orders = $orders->where(
                'delivery_date',
                '<=',
                $to->format('Y-m-d')
            );
        }
        */

        // if ($excludeFulfilled) {
        //     $orders = $orders->where('fulfilled', false);
        // }

        $orders = $orders->where(function ($query) use ($dateRange) {
            $query
                ->where(function ($query1) use ($dateRange) {
                    $query1->where('isMultipleDelivery', 0);
                    if (isset($dateRange['from'])) {
                        $from = Carbon::parse($dateRange['from']);
                        $query1->where(
                            'delivery_date',
                            '>=',
                            $from->format('Y-m-d')
                        );
                    } else {
                        $query1->where(
                            'delivery_date',
                            '>=',
                            Carbon::now()->format('Y-m-d')
                        );
                    }
                    if (isset($dateRange['to'])) {
                        $to = Carbon::parse($dateRange['to']);
                        $query1->where(
                            'delivery_date',
                            '<=',
                            $to->format('Y-m-d')
                        );
                    }
                })
                ->orWhere(function ($query2) use ($dateRange) {
                    $query2
                        ->where('isMultipleDelivery', 1)
                        ->whereHas('meal_orders', function ($subquery) use (
                            $dateRange
                        ) {
                            $subquery->whereNotNull(
                                'meal_orders.delivery_date'
                            );
                            if (isset($dateRange['from'])) {
                                $from = Carbon::parse($dateRange['from']);
                                $subquery->where(
                                    'meal_orders.delivery_date',
                                    '>=',
                                    $from->format('Y-m-d')
                                );
                            } else {
                                $subquery->where(
                                    'meal_orders.delivery_date',
                                    '>=',
                                    Carbon::now()->format('Y-m-d')
                                );
                            }
                            if (isset($dateRange['to'])) {
                                $to = Carbon::parse($dateRange['to']);
                                $subquery->where(
                                    'meal_orders.delivery_date',
                                    '<=',
                                    $to->format('Y-m-d')
                                );
                            }
                        });
                });
        });

        $orders = $orders->get();

        $ingredientsByMeal = [];

        foreach ($orders as $order) {
            $mealOrders = $order->meal_orders()->get();
            foreach ($mealOrders as $mealOrder) {
                $isMultipleDelivery =
                    (int) $mealOrder->order->isMultipleDelivery;

                if ($isMultipleDelivery) {
                    if (!$mealOrder->delivery_date) {
                        continue;
                    }

                    $mealOrder_date = Carbon::parse(
                        $mealOrder->delivery_date
                    )->format('Y-m-d');

                    if (isset($dateRange['from'])) {
                        $from = Carbon::parse($dateRange['from'])->format(
                            'Y-m-d'
                        );
                        if ($mealOrder_date < $from) {
                            continue;
                        }
                    }

                    if (isset($dateRange['to'])) {
                        $to = Carbon::parse($dateRange['to'])->format('Y-m-d');
                        if ($mealOrder_date > $to) {
                            continue;
                        }
                    }
                }

                $quantity = $mealOrder->quantity;
                $meal = $mealOrder->meal;
                $multiplier = 1;
                $mealIngredients = $meal->ingredients;

                // A size was chosen. Use the multiplier
                // 2019-07-24 DB - multipliers no longer used for meal sizes
                //                 store now manually assigns ingredients
                if ($mealOrder->meal_size_id && $mealOrder->meal_size) {
                    $multiplier = 1; //$mealOrder->meal_size->multiplier;
                    $mealIngredients = $mealOrder->meal_size->ingredients;
                }

                foreach ($mealIngredients as $ingredient) {
                    if (!$ingredient->attributes['hidden']) {
                        $adjuster = $ingredient->adjuster / 100;
                        $quantity_unit = $ingredient->pivot->quantity_unit;
                        $quantity_base =
                            $ingredient->pivot->quantity_base *
                            $quantity *
                            $multiplier *
                            $adjuster;

                        $key = $ingredient->id;
                        if ($isMultipleDelivery) {
                            $key =
                                $key .
                                '-' .
                                Carbon::parse(
                                    $mealOrder->delivery_date
                                )->format('Y-m-d');
                            // $ingredient->food_name =
                            //     '(' .
                            //     Carbon::parse(
                            //         $mealOrder->delivery_date
                            //     )->format('D, m/d/y') .
                            //     ') ' .
                            //     $ingredient->food_name;
                        }

                        if (!isset($ingredients[$key])) {
                            $ingredients[$key] = [
                                'id' => $ingredient->id,
                                'ingredient' => $ingredient,
                                'quantity' => $quantity_base,
                                'adjuster' => $adjuster
                            ];
                        } else {
                            $ingredients[$key]['quantity'] += $quantity_base;
                        }
                    }
                }

                $components = collect($mealOrder->components);

                foreach ($components as $component) {
                    if ($component->option) {
                        foreach (
                            $component->option->ingredients
                            as $ingredient
                        ) {
                            if (!$ingredient->attributes['hidden']) {
                                $quantity_unit =
                                    $ingredient->pivot->quantity_unit;
                                $quantity_base =
                                    $ingredient->pivot->quantity_base *
                                    $quantity;
                                //* $multiplier;

                                $key = $ingredient->id;
                                if ($isMultipleDelivery) {
                                    $key =
                                        $key .
                                        '-' .
                                        Carbon::parse(
                                            $mealOrder->delivery_date
                                        )->format('Y-m-d');
                                    $ingredient->food_name =
                                        '(' .
                                        Carbon::parse(
                                            $mealOrder->delivery_date
                                        )->format('D, m/d/y') .
                                        ') ' .
                                        $ingredient->food_name;
                                }
                                if (!isset($ingredients[$key])) {
                                    $ingredients[$key] = [
                                        'id' => $ingredient->id,
                                        'ingredient' => $ingredient,
                                        'quantity' => $quantity_base,
                                        'adjuster' =>
                                            $ingredient->adjuster / 100
                                    ];
                                } else {
                                    $ingredients[$key][
                                        'quantity'
                                    ] += $quantity_base;
                                }
                            }
                        }
                    }
                }

                $addons = collect($mealOrder->addons);

                foreach ($addons as $addon) {
                    if ($addon->addon) {
                        foreach ($addon->addon->ingredients as $ingredient) {
                            if (!$ingredient->attributes['hidden']) {
                                $quantity_unit =
                                    $ingredient->pivot->quantity_unit;
                                $quantity_base =
                                    $ingredient->pivot->quantity_base *
                                    $quantity;
                                //* $multiplier;

                                $key = $ingredient->id;
                                if ($isMultipleDelivery) {
                                    $key =
                                        $key .
                                        '-' .
                                        Carbon::parse(
                                            $mealOrder->delivery_date
                                        )->format('Y-m-d');
                                    $ingredient->food_name =
                                        '(' .
                                        Carbon::parse(
                                            $mealOrder->delivery_date
                                        )->format('D, m/d/y') .
                                        ') ' .
                                        $ingredient->food_name;
                                }

                                if (!isset($ingredients[$key])) {
                                    $ingredients[$key] = [
                                        'id' => $ingredient->id,
                                        'ingredient' => $ingredient,
                                        'quantity' => $quantity_base,
                                        'adjuster' =>
                                            $ingredient->adjuster / 100
                                    ];
                                } else {
                                    $ingredients[$key][
                                        'quantity'
                                    ] += $quantity_base;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $ingredients;
    }

    public function getIngredientsByMeal($dateRange = [])
    {
        $ingredients = [];

        $orders = $this->orders()
            ->with(['meals', 'meals.ingredients', 'meal_orders'])
            ->where(['paid' => 1, 'voided' => 0]);

        if ($dateRange === []) {
            //$orders = $orders->where('delivery_date', $this->getNextDeliveryDate());
        }

        $orders = $orders->where(function ($query) use ($dateRange) {
            $query
                ->where(function ($query1) use ($dateRange) {
                    $query1->where('isMultipleDelivery', 0);
                    if (isset($dateRange['from'])) {
                        $from = Carbon::parse($dateRange['from']);
                        $query1->where(
                            'delivery_date',
                            '>=',
                            $from->format('Y-m-d')
                        );
                    } else {
                        $query1->where(
                            'delivery_date',
                            '>=',
                            Carbon::now()->format('Y-m-d')
                        );
                    }
                    if (isset($dateRange['to'])) {
                        $to = Carbon::parse($dateRange['to']);
                        $query1->where(
                            'delivery_date',
                            '<=',
                            $to->format('Y-m-d')
                        );
                    }
                })
                ->orWhere(function ($query2) use ($dateRange) {
                    $query2
                        ->where('isMultipleDelivery', 1)
                        ->whereHas('meal_orders', function ($subquery) use (
                            $dateRange
                        ) {
                            $subquery->whereNotNull(
                                'meal_orders.delivery_date'
                            );
                            if (isset($dateRange['from'])) {
                                $from = Carbon::parse($dateRange['from']);
                                $subquery->where(
                                    'meal_orders.delivery_date',
                                    '>=',
                                    $from->format('Y-m-d')
                                );
                            } else {
                                $subquery->where(
                                    'meal_orders.delivery_date',
                                    '>=',
                                    Carbon::now()->format('Y-m-d')
                                );
                            }
                            if (isset($dateRange['to'])) {
                                $to = Carbon::parse($dateRange['to']);
                                $subquery->where(
                                    'meal_orders.delivery_date',
                                    '<=',
                                    $to->format('Y-m-d')
                                );
                            }
                        });
                });
        });

        $orders = $orders->get();

        $ingredientsByMeal = [];

        foreach ($orders as $order) {
            $mealOrders = $order->meal_orders()->get();
            foreach ($mealOrders as $mealOrder) {
                $isMultipleDelivery =
                    (int) $mealOrder->order->isMultipleDelivery;

                if ($isMultipleDelivery) {
                    if (!$mealOrder->delivery_date) {
                        continue;
                    }

                    $mealOrder_date = Carbon::parse(
                        $mealOrder->delivery_date
                    )->format('Y-m-d');

                    if (isset($dateRange['from'])) {
                        $from = Carbon::parse($dateRange['from'])->format(
                            'Y-m-d'
                        );
                        if ($mealOrder_date < $from) {
                            continue;
                        }
                    }

                    if (isset($dateRange['to'])) {
                        $to = Carbon::parse($dateRange['to'])->format('Y-m-d');
                        if ($mealOrder_date > $to) {
                            continue;
                        }
                    }
                }

                $quantity = $mealOrder->quantity;
                $meal = $mealOrder->meal;
                $multiplier = 1;
                $mealIngredients = $meal->ingredients;

                // A size was chosen. Use the multiplier
                // 2019-07-24 DB - multipliers no longer used for meal sizes
                //                 store now manually assigns ingredients
                if ($mealOrder->meal_size_id && $mealOrder->meal_size) {
                    $multiplier = 1; //$mealOrder->meal_size->multiplier;
                    $mealIngredients = $mealOrder->meal_size->ingredients;
                }

                for ($i = 0; $i < $quantity; $i++) {
                    foreach ($mealIngredients as $ingredient) {
                        if (!$ingredient->attributes['hidden']) {
                            if (
                                !isset(
                                    $ingredientsByMeal[$mealOrder->short_title][
                                        $ingredient->food_name
                                    ]
                                )
                            ) {
                                $ingredientsByMeal[$mealOrder->short_title][
                                    $ingredient->food_name
                                ] = [
                                    $ingredient->pivot->quantity_unit =>
                                        $ingredient->pivot->quantity
                                ];
                            } else {
                                if (
                                    !isset(
                                        $ingredientsByMeal[
                                            $mealOrder->short_title
                                        ][$ingredient->food_name][
                                            $ingredient->pivot->quantity_unit
                                        ]
                                    )
                                ) {
                                    $ingredientsByMeal[$mealOrder->short_title][
                                        $ingredient->food_name
                                    ][$ingredient->pivot->quantity_unit] =
                                        $ingredient->pivot->quantity;
                                } else {
                                    $ingredientsByMeal[$mealOrder->short_title][
                                        $ingredient->food_name
                                    ][$ingredient->pivot->quantity_unit] +=
                                        $ingredient->pivot->quantity;
                                }
                            }
                        }
                    }

                    $components = collect($mealOrder->components);

                    foreach ($components as $component) {
                        if ($component->option) {
                            foreach (
                                $component->option->ingredients
                                as $ingredient
                            ) {
                                if (!$ingredient->attributes['hidden']) {
                                    if (
                                        !isset(
                                            $ingredientsByMeal[
                                                $mealOrder->short_title
                                            ][$ingredient->food_name]
                                        )
                                    ) {
                                        $ingredientsByMeal[
                                            $mealOrder->short_title
                                        ][$ingredient->food_name] = [
                                            $ingredient->pivot->quantity_unit =>
                                                $ingredient->pivot->quantity
                                        ];
                                    } else {
                                        if (
                                            !isset(
                                                $ingredientsByMeal[
                                                    $mealOrder->short_title
                                                ][$ingredient->food_name][
                                                    $ingredient->pivot
                                                        ->quantity_unit
                                                ]
                                            )
                                        ) {
                                            $ingredientsByMeal[
                                                $mealOrder->short_title
                                            ][$ingredient->food_name][
                                                $ingredient->pivot->quantity_unit
                                            ] = $ingredient->pivot->quantity;
                                        } else {
                                            $ingredientsByMeal[
                                                $mealOrder->short_title
                                            ][$ingredient->food_name][
                                                $ingredient->pivot->quantity_unit
                                            ] += $ingredient->pivot->quantity;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $addons = collect($mealOrder->addons);

                    foreach ($addons as $addon) {
                        if ($addon->addon) {
                            foreach (
                                $addon->addon->ingredients
                                as $ingredient
                            ) {
                                if (!$ingredient->attributes['hidden']) {
                                    if (
                                        !isset(
                                            $ingredientsByMeal[
                                                $mealOrder->short_title
                                            ][$ingredient->food_name]
                                        )
                                    ) {
                                        $ingredientsByMeal[
                                            $mealOrder->short_title
                                        ][$ingredient->food_name] = [
                                            $ingredient->pivot->quantity_unit =>
                                                $ingredient->pivot->quantity
                                        ];
                                    } else {
                                        if (
                                            !isset(
                                                $ingredientsByMeal[
                                                    $mealOrder->short_title
                                                ][$ingredient->food_name][
                                                    $ingredient->pivot
                                                        ->quantity_unit
                                                ]
                                            )
                                        ) {
                                            $ingredientsByMeal[
                                                $mealOrder->short_title
                                            ][$ingredient->food_name][
                                                $ingredient->pivot->quantity_unit
                                            ] = $ingredient->pivot->quantity;
                                        } else {
                                            $ingredientsByMeal[
                                                $mealOrder->short_title
                                            ][$ingredient->food_name][
                                                $ingredient->pivot->quantity_unit
                                            ] += $ingredient->pivot->quantity;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $ingredientsByMeal;
    }

    public function getOrderMeals($dateRange = [])
    {
        $meals = [];

        $orders = $this->orders()
            ->with(['meals'])
            ->where('paid', 1);
        if ($dateRange === []) {
            $orders = $orders->where(
                'delivery_date',
                $this->getNextDeliveryDate()
            );
        }
        if (isset($dateRange['from'])) {
            $from = Carbon::parse($dateRange['from']);
            $orders = $orders->where(
                'delivery_date',
                '>=',
                $from->format('Y-m-d')
            );
        }
        if (isset($dateRange['to'])) {
            $to = Carbon::parse($dateRange['to']);
            $orders = $orders->where(
                'delivery_date',
                '<=',
                $to->format('Y-m-d')
            );
        }
        $orders = $orders->get();

        foreach ($orders as $order) {
            foreach ($order->meals as $meal) {
                $key = $meal->id;

                if (!isset($meals[$key])) {
                    $meals[$key] = [
                        'id' => $key,
                        'meal' => $meal,
                        'quantity' => 1
                    ];
                } else {
                    $meals[$key]['quantity']++;
                }
            }
        }

        return $meals;
    }

    public function getNextDeliveryDay($weekIndex)
    {
        $week = date('D', strtotime("Sunday +{$weekIndex} days"));
        $date = new Carbon('next ' . $week, $this->settings->timezone);
        $date->setTime(0, 0, 0);
        return $date->setTimezone('utc');
    }

    public function getNextDeliveryDate($factorCutoff = false)
    {
        if (!$this->settings) {
            return null;
        }
        return $this->settings->getNextDeliveryDates($factorCutoff)[0] ?? null;
    }

    public function getNextCutoffDate($weekIndex = null)
    {
        if (is_null($weekIndex)) {
            $date = $this->getNextDeliveryDate(false);
        } else {
            $date = $this->getNextDeliveryDay($weekIndex);
        }

        return $date ? $this->getCutoffDate($date) : null;
    }

    /**
     * Get the cutoff date for a particular delivery date
     *
     * @param Carbon $deliveryDate
     * @param DeliveryDay $deliveryDay
     * @return Carbon $cutoffDate
     */
    public function getCutoffDate(
        Carbon $deliveryDate,
        DeliveryDay $deliveryDay = null
    ) {
        $settings = $this->settings;

        if ($deliveryDay) {
            $settings->setDeliveryDayContext($deliveryDay);
        }

        $cutoffDate = Carbon::createFromDate(
            $deliveryDate->year,
            $deliveryDate->month,
            $deliveryDate->day,
            $settings->timezone
        );
        if ($settings->cutoff_type === 'timed') {
            return $cutoffDate
                ->setTime(0, 0, 0)
                ->subSeconds($this->getCutoffSeconds())
                ->setTimezone('utc');
        } elseif ($settings->cutoff_type === 'single_day') {
            $dayName = date(
                'l',
                strtotime("Sunday +{$settings->cutoff_days} days")
            );

            return $cutoffDate
                ->modify('last ' . $dayName)
                ->setTime($settings->cutoff_hours, 0, 0)
                ->setTimezone('utc');
        }

        $settings->clearDeliveryDayContext();
    }

    public function getOrders(
        $groupBy = null,
        $dateRange = [],
        $onlyUnfulfilled = false,
        $onlyPaid = true,
        $onlyDelivery = false,
        $orderDates = false,
        $couponCode = '',
        $removeManualOrders = false,
        $removeCashOrders = false
    ) {
        $orders = Order::whereIn(
            'store_id',
            $this->active_child_store_ids
        )->with(['meals', 'meal_orders']);

        if ($orderDates === false) {
            $orders = $orders->where(function ($query) use (
                $dateRange,
                $onlyPaid,
                $onlyDelivery,
                $couponCode,
                $removeManualOrders,
                $removeCashOrders
            ) {
                if ($onlyPaid) {
                    $query->where('paid', 1);
                }
                if ($onlyDelivery) {
                    $query->where('pickup', 0)->where('shipping', 0);
                }
                if ($couponCode != '') {
                    $query->where('couponCode', $couponCode);
                }
                if ($removeManualOrders) {
                    $query->where('manual', 0);
                }
                if ($removeCashOrders) {
                    $query->where('cashOrder', 0);
                }

                $query->where(function ($innerQuery) use ($dateRange) {
                    $innerQuery
                        ->where(function ($query1) use ($dateRange) {
                            $query1->where('isMultipleDelivery', 0);

                            if (isset($dateRange['from'])) {
                                $from = Carbon::parse($dateRange['from']);
                                $query1->where(
                                    'delivery_date',
                                    '>=',
                                    $from->format('Y-m-d')
                                );
                            }

                            if (isset($dateRange['to'])) {
                                $to = Carbon::parse($dateRange['to']);
                                $query1->where(
                                    'delivery_date',
                                    '<=',
                                    $to->format('Y-m-d')
                                );
                            }
                        })
                        ->orWhere(function ($query2) use ($dateRange) {
                            $query2->where('isMultipleDelivery', 1);

                            $query2->whereHas('meal_orders', function (
                                $subquery1
                            ) use ($dateRange) {
                                $subquery1->whereNotNull(
                                    'meal_orders.delivery_date'
                                );

                                if (isset($dateRange['from'])) {
                                    $from = Carbon::parse($dateRange['from']);
                                    $subquery1->where(
                                        'meal_orders.delivery_date',
                                        '>=',
                                        $from->format('Y-m-d')
                                    );
                                }

                                if (isset($dateRange['to'])) {
                                    $to = Carbon::parse($dateRange['to']);
                                    $subquery1->where(
                                        'meal_orders.delivery_date',
                                        '<=',
                                        $to->format('Y-m-d')
                                    );
                                }
                            });
                        });
                });
            });
        } else {
            if (isset($dateRange['from'])) {
                $from = Carbon::parse($dateRange['from']);
                $orders = $orders->where(
                    'created_at',
                    '>=',
                    $from->format('Y-m-d')
                );
            }

            if (isset($dateRange['to'])) {
                $to = Carbon::parse($dateRange['to']);
                $orders = $orders->where(
                    'created_at',
                    '<=',
                    $to->addDays(1)->format('Y-m-d')
                );
            }

            if (isset($dateRange['startTime'])) {
                $orders = $orders->where(
                    'transferTime',
                    '>=',
                    $dateRange['startTime']
                );
            }

            if (isset($dateRange['endTime'])) {
                $orders = $orders->where(
                    'transferTime',
                    '<=',
                    $dateRange['endTime']
                );
            }

            if ($onlyPaid) {
                $orders = $orders->where('paid', 1);
            }
            if ($onlyDelivery) {
                $orders = $orders->where('pickup', 0);
            }
            if ($couponCode != '') {
                $orders = $orders->where('couponCode', $couponCode);
            }
            if ($removeManualOrders) {
                $orders = $orders->where('manual', 0);
            }
            if ($removeCashOrders) {
                $orders = $orders->where('cashOrder', 0);
            }
        }

        // Disabled Old Workflow
        /*$orders = $this->orders()->with(['meals', 'meal_orders']);

        $date = '';
        if ($orderDates === false) {
            $date = 'delivery_date';
        } else {
            $date = 'created_at';
        }

        if (isset($dateRange['from'])) {
            $from = Carbon::parse($dateRange['from']);
            $orders = $orders->where($date, '>=', $from->format('Y-m-d'));
        }
        if (isset($dateRange['to'])) {
            $to = Carbon::parse($dateRange['to']);

            if ($date == 'created_at') {
                $orders = $orders->where(
                    $date,
                    '<=',
                    $to->addDays(1)->format('Y-m-d')
                );
            } else {
                $orders = $orders->where($date, '<=', $to->format('Y-m-d'));
            }
        }*/

        // if ($onlyUnfulfilled) {
        //     $orders = $orders->where('fulfilled', 0);
        // }

        if (!is_a($orders, 'Illuminate\Database\Eloquent\Collection')) {
            $orders = $orders->get();
        }

        if (isset($dateRange['startTime'])) {
            $startTime = Carbon::parse($dateRange['startTime'])
                ->subMinutes('1')
                ->format('H:i:s');

            $orders = $orders->filter(function ($order) use ($startTime) {
                $transferTime = Carbon::parse(
                    substr($order->transferTime, 0, 8)
                )->format('H:i:s');
                if ($transferTime >= $startTime) {
                    return $order;
                }
            });
        }

        if (isset($dateRange['endTime'])) {
            $endTime = Carbon::parse($dateRange['endTime'])
                ->addMinutes('1')
                ->format('H:i:s');
            $orders = $orders->filter(function ($order) use ($endTime) {
                $transferTime = Carbon::parse(
                    substr($order->transferTime, 0, 8)
                )->format('H:i:s');
                if ($transferTime <= $endTime) {
                    return $order;
                }
            });
        }

        if ($groupBy) {
            $orders = $orders->groupBy($groupBy);
        }

        return $orders;
    }

    public function getOrdersForNextDelivery($groupBy = null)
    {
        // Not in use
        $date = $this->getNextDeliveryDate();

        $orders = $this->orders()->with('meals');
        $orders = $orders->where(function ($query) use ($date) {
            $query
                ->where(function ($query1) use ($date) {
                    $query1->where('isMultipleDelivery', 0)->where('paid', 1);
                    $query1->where('delivery_date', $date->format('Y-m-d'));
                })
                ->orWhere(function ($query2) use ($date) {
                    $query2
                        ->where('isMultipleDelivery', 1)
                        ->where('paid', 1)
                        ->whereHas('meal_orders', function ($subquery1) use (
                            $date
                        ) {
                            $subquery1->whereNotNull(
                                'meal_orders.delivery_date'
                            );

                            $subquery1->where(
                                'meal_orders.delivery_date',
                                $date->format('Y-m-d')
                            );
                        })
                        ->orWhereHas('meal_package_orders', function (
                            $subquery2
                        ) use ($date) {
                            $subquery2->whereNotNull(
                                'meal_package_orders.delivery_date'
                            );

                            $subquery2->where(
                                'meal_package_orders.delivery_date',
                                $date->format('Y-m-d')
                            );
                        });
                });
        });

        $orders = $orders->get();

        // Disabled Old Workflow
        /*$orders = $this->orders()
            ->with('meals')
            ->where([['paid', 1], ['delivery_date', $date->format('Y-m-d')]])
            ->get();*/

        if ($groupBy) {
            $orders = $orders->groupBy($groupBy);
        }

        return $orders;
    }

    public function getPastOrders($groupBy = null)
    {
        // Not in use
        $date = $this->getNextDeliveryDate();

        $orders = $this->orders()->with('meals');

        $orders = $orders
            ->where(function ($query) use ($dateRange) {
                $query->where('isMultipleDelivery', 0)->where('paid', 1);

                $query->where('delivery_date', '<', $date->format('Y-m-d'));
            })
            ->orWhere(function ($query) use ($dateRange) {
                $query
                    ->where('isMultipleDelivery', 1)
                    ->where('paid', 1)
                    ->whereHas('meal_orders', function ($subquery1) use (
                        $dateRange
                    ) {
                        $subquery1->whereNotNull('meal_orders.delivery_date');

                        $subquery1->where(
                            'meal_orders.delivery_date',
                            '<',
                            $date->format('Y-m-d')
                        );
                    })
                    ->orWhereHas('meal_package_orders', function (
                        $subquery2
                    ) use ($dateRange) {
                        $subquery2->whereNotNull(
                            'meal_package_orders.delivery_date'
                        );

                        $subquery2->where(
                            'meal_package_orders.delivery_date',
                            '<',
                            $date->format('Y-m-d')
                        );
                    });
            });

        $orders = $orders->get();

        /*$orders = $this->orders()
            ->with('meals')
            ->where([
                ['paid', 1],
                ['delivery_date', '<', $date->format('Y-m-d')]
            ])
            ->get();*/

        if ($groupBy) {
            $orders = $orders->groupBy($groupBy);
        }

        return $orders;
    }

    public function getFulfilledOrders($groupBy = null)
    {
        $date = $this->getNextDeliveryDate();
        $orders = $this->orders()
            ->with('meals')
            ->where([['paid', 1], ['fulfilled', '1']])
            ->get();

        if ($groupBy) {
            $orders = $orders->groupBy($groupBy);
        }

        return $orders;
    }

    public function deliversToZip($zip)
    {
        // return true;
        $zip = strtoupper(strval($zip));
        $zip = preg_replace("/\s+/", "", $zip);
        list($zip) = explode(' ', $zip);

        foreach ($this->deliveryFeeZipCodes as $zipcode) {
            $zipcode = strtoupper(strval($zipcode->zip_code));
            $zipcode = preg_replace("/\s+/", "", $zipcode);
            list($zipcode) = explode(' ', $zipcode);
            if ($zip === $zipcode) {
                return true;
            }
        }
        if ($this->settings && $this->settings->delivery_distance_zipcodes) {
            foreach ($this->settings->delivery_distance_zipcodes as $zipcode) {
                $zipcode = strtoupper(strval($zipcode));
                $zipcode = preg_replace("/\s+/", "", $zipcode);
                list($zipcode) = explode(' ', $zipcode);
                if ($zip === $zipcode) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasStripe()
    {
        return isset($this->settings->stripe_id) && $this->settings->stripe_id;
    }

    public function notificationEnabled($notif)
    {
        if (!$this->settings) {
            return false;
        }
        return $this->settings->notificationEnabled($notif);
    }

    public function setTimezone()
    {
        $store = $this;

        $settings = $store->settings;
        if ($settings && $settings->timezone) {
            $timezone = $settings->timezone;
            date_default_timezone_set($timezone);
        }
    }

    public function sendNotification($notif, $data = [])
    {
        $store = $this;
        $storeDetails = $this->details;

        $email = null;

        /* Timezone */
        $settings = $store->settings;
        if ($settings && $settings->timezone) {
            $timezone = $settings->timezone;
            date_default_timezone_set($timezone);
        }
        /* Timezone End */

        if (isset($store->modules) && isset($store->modules->emailBranding)) {
            $emailBranding = (int) $store->modules->emailBranding;

            if ($emailBranding == 1) {
                $logo = $storeDetails->getMedia('logo')->first();

                if ($logo) {
                    $path = $logo->getPath('thumb');

                    if (file_exists($path)) {
                        $logo_b64 = \App\Utils\Images::encodeB64($path);

                        if ($logo_b64) {
                            $data['logo_b64'] = $logo_b64;
                        }
                    }
                }
            }
        }

        switch ($notif) {
            case 'signup_checkin':
                $email = new SignupCheckin($data);
                break;
            case 'new_order':
                $email = new NewOrder($data);
                $bcc = true;
                break;

            case 'new_subscription':
                $email = new NewSubscription($data);
                $bcc = true;
                break;

            case 'cancelled_subscription':
                $email = new CancelledSubscription($data);
                break;

            case 'ready_to_print':
                $email = new ReadyToPrint([
                    'store' => $store,
                    'storeDetails' => $storeDetails
                ]);
                break;

            case 'adjusted_order':
                $email = new AdjustedOrder($data);
                break;

            case 'new_gift_card':
                $email = new NewGiftCard($data);
                $recipient = $data['emailRecipient'];
                break;
        }

        if ($email && !isset($recipient)) {
            try {
                Mail::to($this->user)->send($email);
                return true;
            } catch (\Exception $e) {
            }
        } elseif ($email && isset($recipient)) {
            Mail::to($recipient)->send($email);
            return true;
        }

        return false;
    }

    public function getCutoffSeconds()
    {
        $cutoff =
            $this->settings->cutoff_days * (60 * 60 * 24) +
            $this->settings->cutoff_hours * (60 * 60);
        return $cutoff;
    }

    /**
     * Returns whether the store's cutoff passed
     * @param mixed $period
     * @return boolean
     */
    public function cutoffPassed($period = 'hour')
    {
        if (!$this->settings || !is_array($this->settings->delivery_days)) {
            return false;
        }

        $now = Carbon::now('utc');

        $cutoff =
            $this->settings->cutoff_days * (60 * 60 * 24) +
            $this->settings->cutoff_hours * (60 * 60);

        if ($this->settings->cutoff_type === 'timed') {
            foreach ($this->settings->delivery_days as $day) {
                $date = Carbon::createFromFormat(
                    'D',
                    $day,
                    $this->settings->timezone
                )->setTime(0, 0, 0);
                $diff = $date->getTimestamp() - $now->getTimestamp() - $cutoff;
                //echo $diff."\r\n";

                // Cutoff passed less than an hour ago
                if ($period === 'hour' && $diff >= -60 * 60 && $diff < 0) {
                    return true;
                }
            }
        } elseif ($this->settings->cutoff_type === 'single_day') {
            /*$date = $this->getNextDeliveryDate();
            if (!$date) {
                return false;
            }
            $cutoff = $this->getCutoffDate($date);*/

            $dayName = date(
                'l',
                strtotime("Sunday +{$this->settings->cutoff_days} days")
            );

            $cutoff = Carbon::parse(
                "this $dayName",
                $this->settings->timezone
            )->setTime($this->settings->cutoff_hours, 0, 0);

            $diff = $cutoff->getTimestamp() - $now->getTimestamp();

            // Cutoff passed less than an hour ago
            if ($period === 'hour' && $diff >= -60 * 60 && $diff < 0) {
                return true;
            }
        }

        return false;
    }

    public function getStatusAttribute()
    {
        return $this->plan ? $this->plan->status : null;
    }

    public function getActiveChildStoreIdsAttribute()
    {
        $childStoreIds = $this->childStores
            ->where('settings.activeForParent', true)
            ->pluck('id');
        $childStoreIds->push($this->id);
        return $childStoreIds;
    }

    public function getCutoffPassedAttribute()
    {
        $now = Carbon::now('utc');
        $date = $this->getNextDeliveryDate();
        if (!$date) {
            return false;
        }
        $cutoff = $this->getCutoffDate($date);

        if ($cutoff != false) {
            return $cutoff->isPast();
        } else {
            return false;
        }
    }

    public function getNextLockedInDate($period = 'hour')
    {
        if (!$this->settings || !is_array($this->settings->delivery_days)) {
            return false;
        }

        $now = Carbon::now('utc');

        $cutoff =
            $this->settings->cutoff_days * (60 * 60 * 24) +
            $this->settings->cutoff_hours * (60 * 60);

        if ($this->settings->cutoff_type === 'timed') {
            foreach ($this->settings->delivery_days as $day) {
                $date = Carbon::createFromFormat(
                    'D',
                    $day,
                    $this->settings->timezone
                )->setTime(0, 0, 0);
                $diff = $date->getTimestamp() - $now->getTimestamp() - $cutoff;
                //echo $diff."\r\n";

                // Cutoff passed less than an hour ago
                if ($period === 'hour' && $diff >= -60 * 60 && $diff < 0) {
                    if (isset($day)) {
                        return $day;
                    }
                }
            }
        } elseif ($this->settings->cutoff_type === 'single_day') {
            /*$date = $this->getNextDeliveryDate();
            if (!$date) {
                return false;
            }
            $cutoff = $this->getCutoffDate($date);*/

            $dayName = date(
                'l',
                strtotime("Sunday +{$this->settings->cutoff_days} days")
            );

            $cutoff = Carbon::parse(
                "this $dayName",
                $this->settings->timezone
            )->setTime($this->settings->cutoff_hours, 0, 0);

            $diff = $cutoff->getTimestamp() - $now->getTimestamp();

            // Cutoff passed less than an hour ago
            if ($period === 'hour' && $diff >= -60 * 60 && $diff < 0) {
                if (isset($dayName)) {
                    return $dayName;
                }
            }
        }
    }

    public function getNextLockedInDateAttribute()
    {
        $date = $this->getNextLockedInDate();
        return $date;
    }

    public function getNextCutoffDateAttribute()
    {
        $date = $this->getNextCutoffDate();
        return $date ? $date->toDateTimeString() : null;
    }

    public function getNextDeliveryDateAttribute()
    {
        $date = $this->getNextDeliveryDate();
        return $date ? $date->toDateTimeString() : null;
    }

    public function getUrlAttribute()
    {
        return $this->getUrl();
    }

    public function isModuleEnabled($module)
    {
        return $this->modules[$module];
    }

    public function disableNextWeekOrders()
    {
        $settings = StoreSetting::where('store_id', $this->id)->first();
        $settings->preventNextWeekOrders = 1;
        $settings->update();
    }

    public function enableNextWeekOrders()
    {
        $settings = StoreSetting::where('store_id', $this->id)->first();
        $settings->preventNextWeekOrders = 0;
        $settings->update();
    }

    public function getHasPromoCodesAttribute()
    {
        if (
            $this->coupons()->count() > 0 ||
            $this->purchasedGiftCards()->count() > 0
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function getBulkCustomersAttribute()
    {
        if ($this->customers->count() >= 200) {
            return true;
        } else {
            return false;
        }
    }

    public function getHasDeliveryDayItemsAttribute()
    {
        if (
            $this->deliveryDayMeals()->count() > 0 ||
            $this->deliveryDayMealPackages()->count() > 0
        ) {
            return true;
        }
        return false;
    }
}
