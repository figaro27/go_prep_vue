<?php
namespace App\Http\Controllers;

use App\Allergy;
use App\MealTag;
use App\Store;
use App\Meal;
use App\OptimizedMeal;
use App\OptimizedMealPackage;
use App\MealPackage;
use App\MealPackageSize;
use App\Category;
use App\DeliveryDay;
use App\DeliveryDayMeal;
use App\DeliveryDayMealPackage;
use App\GiftCard;
use App\StoreSetting;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Resources\{DeliveryDayResource, DeliveryDayCollection};
use Exception;

class SpaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('store_slug');
    }

    /**
     * Undocumented function
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            //$this->middleware('auth:api');

            $user = auth('api')->user();
            $context = 'guest';
            $store = null;

            $willDeliver = false;

            if ($user) {
                // Store user
                if ($user->hasRole('store') && $user->has('store')) {
                    $context = 'store';
                } else {
                    $context = 'customer';
                }
            }
            // Not logged in
            else {
                $context = 'guest';
            }

            if ($context === 'guest') {
                $store = defined('STORE_ID')
                    ? Store::with([
                        'meals',
                        'packages',
                        'units',
                        'categories',
                        'settings',
                        'modules',
                        'moduleSettings',
                        'details',
                        'giftCards',
                        'pickupLocations',
                        'lineItems',
                        'referrals',
                        'referralSettings',
                        'promotions',
                        'meals.categories',
                        'meals.allergies',
                        'packages.meals',
                        'packages.sizes',
                        'packages.components',
                        'packages.addons',
                        'meals.sizes',
                        'meals.components',
                        'meals.addons',
                        'meals.macros',
                        'deliveryFeeZipCodes',
                        'deliveryFeeRanges',
                        'holidayTransferTimes',
                        'menuSettings'
                    ])->find(STORE_ID)
                    : null;

                return [
                    'context' => $context,
                    'user' => null,
                    'store' => $store
                ];
            } elseif ($context === 'store') {
                $store = $user
                    ->store()
                    ->with([
                        'categories',
                        'ingredients',
                        'packages',
                        'units',
                        'settings',
                        'storeDetail',
                        'coupons',
                        'giftCards',
                        'purchasedGiftCards',
                        'pickupLocations',
                        'referrals',
                        'referralSettings',
                        'promotions',
                        'productionGroups',
                        'lineItems',
                        'modules',
                        'moduleSettings',
                        'reportSettings',
                        'smsSettings',
                        'deliveryFeeZipCodes',
                        'deliveryFeeRanges',
                        'holidayTransferTimes',
                        'menuSettings'
                    ])
                    ->first();

                $user->storeOwner = true;

                return [
                    'context' => $context,
                    'user' => $user,
                    'store' => $store
                ];
            } elseif ($context === 'customer') {
                $store = defined('STORE_ID')
                    ? Store::with([
                        'meals',
                        'packages',
                        'units',
                        'categories',
                        'settings',
                        'modules',
                        'moduleSettings',
                        'details',
                        'giftCards',
                        'pickupLocations',
                        'referralSettings',
                        'promotions',
                        'meals.categories',
                        'meals.allergies',
                        'packages.meals',
                        'packages.sizes',
                        'packages.components',
                        'packages.addons',
                        'meals.sizes',
                        'meals.components',
                        'meals.addons',
                        'meals.macros',
                        'deliveryFeeZipCodes',
                        'deliveryFeeRanges',
                        'holidayTransferTimes',
                        'menuSettings'
                    ])->find(STORE_ID)
                    : $user->last_viewed_store;

                if ($store) {
                    if ($user) {
                        $distance = $user->distanceFrom($store);
                    } else {
                        $distance = 1;
                    }

                    if ($store->settings->delivery_distance_type === 'radius') {
                        ///$distance = $user->distanceFrom($store);
                        $willDeliver =
                            $distance <
                            $store->settings->delivery_distance_radius;
                    } else {
                        $willDeliver = $store->deliversToZip(
                            $user->userDetail->zip
                        );
                    }

                    $user->last_viewed_store_id = $store->id;
                    $user->save();
                    $user->details->last_viewed_store_id = $store->id;
                    $user->details->save();

                    return [
                        'context' => $context,
                        'user' => $user,
                        'store' => $store,
                        'store_distance' => $distance ?? null,
                        'will_deliver' => $willDeliver
                    ];
                } else {
                    return [
                        'context' => $context,
                        'user' => $user,
                        'store' => null
                    ];
                }
            }
        } else {
            $user = auth()->user();
            return view('app', [
                'store' => Store::find(STORE_ID)
            ]);

            if ($user) {
                if ($user->user_role_id === 2) {
                    return view('store');
                } elseif ($user->user_role_id === 3) {
                    return view('admin');
                } else {
                    return view('customer');
                }
            }

            //return redirect('/login');
        }
    }

    public function optimized(Request $request)
    {
        /** @var \App\User */
        $user = auth('api')->user();

        $store = null;
        $willDeliver = false;

        $last_viewed_store = null;
        if ($user && isset($user->last_viewed_store)) {
            $last_viewed_store = $user->last_viewed_store;
        }

        /* Context */
        $context = 'guest';
        if ($user) {
            if ($user->hasRole('store') && $user->has('store')) {
                $context = 'store';
            } else {
                $context = 'customer';
            }
        } else {
            $context = 'guest';
        }
        /* Context End */

        if ($context == "store") {
            $store = $user
                ->store()
                ->with([
                    'categories',
                    'ingredients',
                    'units',
                    'settings',
                    'storeDetail',
                    'coupons',
                    'giftCards',
                    'referrals',
                    'referralSettings',
                    'promotions',
                    'pickupLocations',
                    'productionGroups',
                    'lineItems',
                    'modules',
                    'moduleSettings',
                    'reportSettings',
                    'smsSettings',
                    'deliveryFeeZipCodes',
                    'deliveryFeeRanges',
                    'deliveryDayZipCodes',
                    'deliveryDays',
                    'holidayTransferTimes',
                    'menuSettings'
                ])
                ->first();

            $user->storeOwner = true;

            return [
                'context' => $context,
                'user' => $user,
                'store' => $store
            ];
        } else {
            $store = defined('STORE_ID')
                ? Store::with([
                    'units',
                    'categories',
                    'settings',
                    'modules',
                    'moduleSettings',
                    'details',
                    'giftCards',
                    'referralSettings',
                    'promotions',
                    'pickupLocations',
                    'deliveryDays',
                    'deliveryFeeZipCodes',
                    'deliveryFeeRanges',
                    'deliveryDayZipCodes',
                    'holidayTransferTimes',
                    'menuSettings'
                ])->find(STORE_ID)
                : $last_viewed_store;

            // Load only delivery day meals & delivery day meal packages in which the associated meal or meal package isn't inactive or deleted
            if ($store) {
                $store->deliveryDayMeals = DeliveryDayMeal::where(
                    'store_id',
                    $store->id
                )
                    ->whereHas('meal', function ($meal) {
                        $meal->where('active', 1)->where('deleted_at', null);
                    })
                    ->get();

                $store->deliveryDayMealPackages = DeliveryDayMealPackage::where(
                    'store_id',
                    $store->id
                )
                    ->whereHas('meal_package', function ($meal_package) {
                        $meal_package
                            ->where('active', 1)
                            ->where('deleted_at', null);
                    })
                    ->get();
            }

            if ($store) {
                $store->details->makeHidden([
                    'phone',
                    'address',
                    'city',
                    'created_at'
                ]);
                $store->settings->makeHidden([
                    'authorize_login_id',
                    'authorize_public_key',
                    'authorize_transaction_key',
                    'fbPixel',
                    'gaCode'
                ]);
            }

            if ($store && $user) {
                try {
                    $distance = $user->distanceFrom($store);
                } catch (Exception $e) {
                    $distance = null;
                }

                if (
                    $store->modules->multipleDeliveryDays &&
                    count($store->deliveryDayZipCodes) > 0
                ) {
                    foreach (
                        $store->deliveryDayZipCodes->toArray()
                        as $deliveryDayZipCode
                    ) {
                        if (
                            (string) $deliveryDayZipCode['zip_code'] ===
                            (string) $user->userDetail->zip
                        ) {
                            $willDeliver = true;
                        }
                    }
                } else {
                    if ($store->settings->delivery_distance_type === 'radius') {
                        $willDeliver =
                            !is_null($distance) &&
                            $distance <
                                $store->settings->delivery_distance_radius;
                    } else {
                        $willDeliver = $store->deliversToZip(
                            $user->userDetail->zip
                        );
                    }
                }

                if ($store->settings->deliveryFeeType === 'range') {
                    $maxRange = 0;
                    foreach ($store->deliveryFeeRanges as $range) {
                        if ($maxRange < $range->ending_miles) {
                            $maxRange = $range->ending_miles;
                        }
                    }

                    if ($distance < $maxRange) {
                        $willDeliver = true;
                    }
                }

                $user->last_viewed_store_id = $store->id;
                $user->save();
                $user->details->last_viewed_store_id = $store->id;
                $user->details->save();

                return [
                    'context' => $context,
                    'user' => $user,
                    'store' => $store,
                    'store_distance' => $distance ?? null,
                    'will_deliver' => $willDeliver
                ];
            } else {
                return [
                    'context' => $context,
                    'user' => $user,
                    'store' => $store
                ];
            }
        }
    }

    public function context(Request $request)
    {
        $user = auth('api')->user();
        $context = 'guest';

        if ($user) {
            // Store user
            if ($user->hasRole('store') && $user->has('store')) {
                $context = 'store';
            } else {
                $context = 'customer';
            }
        }
        // Not logged in
        else {
            $context = 'guest';
        }

        return [
            'context' => $context
        ];
    }

    public function refresh_lazy_store(Request $request)
    {
        // Speed Optimization
        $user = auth('api')->user();

        $store_id = 0;
        if ($user && $user->hasRole('store') && $user->has('store')) {
            $store_id = $user->store->id;
        }

        $limit = 30;
        $offset_meal = $offset_package = $bypass_meal = 0;

        $data = $request->all();
        extract($data);

        $offset_meal = (int) $offset_meal;
        $offset_package = (int) $offset_package;
        $bypass_meal = (int) $bypass_meal;

        $meals = $packages = [];
        $end = 0;

        if ($bypass_meal == 0) {
            $meals = Meal::with([
                'orders',
                'tags',
                'ingredients',
                'sizes',
                'attachments'
            ])
                ->without(['allergies', 'categories', 'store'])
                ->withTrashed()
                ->where('store_id', $store_id)
                ->orderBy('title')
                ->offset($offset_meal)
                ->limit($limit)
                ->get()
                ->toArray();
        }

        $new_limit = $limit;
        if ($meals && count($meals) > 0) {
            $new_limit = $limit - count($meals);
        }

        if ($new_limit > 0) {
            $packages = MealPackage::with(['meals'])
                ->where('store_id', $store_id)
                // ->withTrashed()
                ->orderBy('title')
                ->offset($offset_package)
                ->limit($new_limit)
                ->get()
                ->toArray();

            if (count($packages) > 0) {
                foreach ($packages as &$package) {
                    $package['meal_package'] = true;
                }
            }
        }

        // Set Return Value
        if (count($meals) >= $limit) {
            $offset_meal += $limit;
            $offset_package = $bypass_meal = 0;
        } elseif (count($packages) > 0) {
            if (count($meals) + count($packages) >= $limit) {
                $offset_meal = 0;
                $bypass_meal = 1;
                $offset_package += count($packages);
            } else {
                $end = 1;
            }
        } else {
            $end = 1;
        }

        return [
            'meals' => $meals,
            'packages' => $packages,
            'offset_meal' => $offset_meal,
            'offset_package' => $offset_package,
            'bypass_meal' => $bypass_meal,
            'end' => $end
        ];
    }

    public function refresh_lazy(Request $request)
    {
        // Speed Optimization
        $user = auth('api')->user();

        $store_id = $offset_meal = $offset_package = $delivery_day_id = 0;
        $limit = 30;

        $category_id = 0;
        $category_ids_str = ""; // Full Category Ids

        $bypass_meal = 0;

        $data = $request->all();
        extract($data);

        if ($user && $user->hasRole('store') && $user->has('store')) {
            $store_id = $user->store->id;
        } else {
            if (defined('STORE_ID')) {
                $store_id = (int) STORE_ID;
            } else {
                if ($user && isset($user->last_viewed_store)) {
                    $store_id = (int) $user->last_viewed_store->id;
                }
            }
        }

        $storeSetting = StoreSetting::where('store_id', $store_id)->first();

        $nextDeliveryDayWeekIndex = null;
        if ($storeSetting && $storeSetting->store->hasDeliveryDayItems) {
            $nextDeliveryDayWeekIndex =
                $storeSetting->next_orderable_delivery_dates[0]['week_index'];
        }

        $offset_meal = (int) $offset_meal;
        $offset_package = (int) $offset_package;
        $category_id = (int) $category_id;
        // $delivery_day_id = $request->get('delivery_day_id')
        //     ? (int) $request->get('delivery_day_id')
        //     : DeliveryDay::where([
        //         'store_id' => $store_id,
        //         'day' => $nextDeliveryDayWeekIndex
        //     ])
        //         ->pluck('id')
        //         ->first();
        // Switching showing menu items by delivery day on front end.
        $delivery_day_id = null;
        $category_ids =
            trim($category_ids_str) == ""
                ? []
                : explode(",", trim($category_ids_str));

        $category_data = $delivery_day = null;
        if ($delivery_day_id != 0) {
            $delivery_day = DeliveryDay::find($delivery_day_id);
            if ($delivery_day) {
                $delivery_day->has_items = true;
            }
        }

        $items = $meals = $packages = $giftCards = []; // Both of meals and packages
        $end = 0;

        if ($store_id != 0) {
            $isValidDD = false;
            if ($delivery_day_id != 0) {
                /*$delivery_day_meals = DeliveryDayMeal::has('meal.categories')->where(
                'delivery_day_id',
                $delivery_day_id
              )->first();

              $delivery_day_meal_packages = DeliveryDayMealPackage::has('meal_package.categories')->where(
                  'delivery_day_id',
                  $delivery_day_id
              )->first();

              $isValidDD = ($delivery_day_meals || $delivery_day_meal_packages) ? true : false;*/
                $isValidDD = true;
            }

            /* Building Categories */
            if ($category_id == 0 || count($category_ids) == 0) {
                $categories = Category::select(
                    'active',
                    'store_id',
                    'id',
                    'category',
                    'subtitle',
                    'order',
                    'date_range',
                    'date_range_exclusive',
                    'date_range_from',
                    'date_range_to',
                    'minimumType',
                    'minimum',
                    'minimumIfAdded'
                )->where(['store_id' => $store_id, 'activeForStore' => 1]);

                if ($user === null || $user->user_role_id === 1) {
                    $categories = $categories->where('active', 1);
                }

                $categories = $categories->orderBy('order')->get();

                if ($categories && count($categories) > 0) {
                    foreach ($categories as $category) {
                        $temp_id = (int) $category->id;

                        $temp_meal = Meal::whereHas('categories', function (
                            $query
                        ) use ($temp_id) {
                            $query->where('categories.id', $temp_id);
                        })->where([
                            'store_id' => $store_id,
                            'deleted_at' => null
                        ]);

                        if ($delivery_day_id != 0 && $isValidDD) {
                            $temp_meal = $temp_meal
                                ? $temp_meal
                                    ->doesntHave('days')
                                    ->orWhereHas('days', function ($query) use (
                                        $delivery_day_id
                                    ) {
                                        $query->where(
                                            'delivery_day_meals.delivery_day_id',
                                            $delivery_day_id
                                        );
                                    })
                                : null;
                        }

                        $temp_meal = $temp_meal->first();

                        $temp_package = OptimizedMealPackage::whereHas(
                            'categories',
                            function ($query) use ($temp_id) {
                                $query->where('categories.id', $temp_id);
                            }
                        )->where([
                            'store_id' => $store_id,
                            'deleted_at' => null
                        ]);

                        $temp_package = $temp_package->first();

                        $temp_giftCard = GiftCard::whereHas(
                            'categories',
                            function ($query) use ($temp_id) {
                                $query->where('categories.id', $temp_id);
                            }
                        )->where([
                            'store_id' => $store_id,
                            'deleted_at' => null
                        ]);

                        if ($delivery_day_id != 0 && $isValidDD) {
                            $temp_package = $temp_package
                                ? $temp_package
                                    ->doesntHave('days')
                                    ->orWhereHas('days', function ($query) use (
                                        $delivery_day_id
                                    ) {
                                        $query->where(
                                            'delivery_day_meal_packages.delivery_day_id',
                                            $delivery_day_id
                                        );
                                    })
                                : null;
                        }

                        $temp_giftCard = $temp_giftCard->first();

                        if ($temp_meal || $temp_package || $temp_giftCard) {
                            // Meal or Package exists
                            $category_ids[] = $temp_id;

                            if (!$category_data) {
                                $category_data = [];
                            }

                            $category_data[] = $category;
                        }
                    }

                    if (count($category_ids) > 0) {
                        $category_id = (int) $category_ids[0];
                        $category_ids_str = implode(',', $category_ids);
                    }
                }
            }

            /* Building Categories End */
            if ($category_id != 0 && count($category_ids) > 0) {
                $meals = [];
                $packages = [];
                if ($bypass_meal == 0) {
                    $meals = Meal::where('active', 1)
                        ->with([
                            'allergies',
                            'sizes',
                            'tags',
                            'components',
                            'addons',
                            'macros'
                        ])
                        ->whereHas('categories', function ($query) use (
                            $category_id
                        ) {
                            $query->where('categories.id', $category_id);
                        })
                        ->where([
                            'store_id' => $store_id,
                            'deleted_at' => null
                        ]);

                    if ($delivery_day_id != 0 && $isValidDD) {
                        $meals = $meals
                            ->doesntHave('days')
                            ->orWhereHas('days', function ($query) use (
                                $delivery_day_id
                            ) {
                                $query->where(
                                    'delivery_day_meals.delivery_day_id',
                                    $delivery_day_id
                                );
                            })
                            ->whereHas('categories', function ($query) use (
                                $category_id
                            ) {
                                $query->where('categories.id', $category_id);
                            });
                    }

                    $meals = $meals
                        ->orderBy('title')
                        ->offset($offset_meal)
                        ->limit($limit)
                        ->get()
                        ->toArray();
                }

                $new_limit = $limit;
                if ($meals && count($meals) > 0) {
                    $new_limit = $limit - count($meals);
                }

                if ($new_limit > 0) {
                    $packages = OptimizedMealPackage::with(['sizes'])
                        ->whereHas('categories', function ($query) use (
                            $category_id
                        ) {
                            $query->where('categories.id', $category_id);
                        })
                        ->where([
                            'store_id' => $store_id,
                            'deleted_at' => null
                        ]);

                    if ($delivery_day_id != 0 && $isValidDD) {
                        $packages = $packages
                            ->doesntHave('days')
                            ->orWhereHas('days', function ($query) use (
                                $delivery_day_id
                            ) {
                                $query->where(
                                    'delivery_day_meal_packages.delivery_day_id',
                                    $delivery_day_id
                                );
                            })
                            ->whereHas('categories', function ($query) use (
                                $category_id
                            ) {
                                $query->where('categories.id', $category_id);
                            });
                    }

                    $packages = $packages
                        ->orderBy('title')
                        ->offset($offset_package)
                        ->limit($new_limit)
                        ->get()
                        ->toArray();

                    if (count($packages) > 0) {
                        foreach ($packages as &$package) {
                            $package['meal_package'] = true;

                            if ($delivery_day && $delivery_day_id != 0) {
                                $package['delivery_day'] = $delivery_day;
                            }
                        }
                    }
                }

                $new_limit = $limit;
                if ($packages && count($packages) > 0) {
                    $new_limit = $limit - count($packages);
                }

                if ($new_limit > 0) {
                    $giftCards = GiftCard::whereHas('categories', function (
                        $query
                    ) use ($category_id) {
                        $query->where('categories.id', $category_id);
                    })->where([
                        'store_id' => $store_id
                    ]);

                    $giftCards = $giftCards
                        ->orderBy('title')
                        ->limit($new_limit)
                        ->get()
                        ->toArray();
                }

                /* Set Delivery Day */
                if ($delivery_day && $delivery_day_id != 0) {
                    if (count($meals)) {
                        foreach ($meals as &$meal) {
                            $meal['delivery_day'] = $delivery_day;
                        }
                    }
                }
                /* Set Delivery Day End */

                /* Set Return Value */
                $next = false;
                if (count($meals) == 0 && count($packages) == 0) {
                    // Next
                    $items = [];
                    $next = true;
                } elseif (count($meals) > 0 && count($packages) > 0) {
                    $items = array_merge($meals, $packages);

                    if (count($items) >= $limit) {
                        $offset_meal = 0;
                        $offset_package = $limit - count($meals);
                        $bypass_meal = 1;
                    } else {
                        // Next
                        $next = true;
                    }
                } elseif (count($packages) > 0) {
                    $items = $packages;

                    if (count($items) >= $limit) {
                        $offset_meal = 0;
                        $offset_package += $limit;
                        $bypass_meal = 1;
                    } else {
                        // Next
                        $next = true;
                    }
                } elseif (count($meals) > 0) {
                    $items = $meals;

                    if (count($items) >= $limit) {
                        $offset_meal += $limit;
                        $offset_package = 0;
                        $bypass_meal = 0;
                    } else {
                        // Next
                        $next = true;
                    }
                }

                if (count($giftCards)) {
                    $items = array_merge($items, $giftCards);
                }

                if ($next) {
                    $offset_meal = $offset_package = 0;
                    $bypass_meal = 0;

                    $key = (int) array_search($category_id, $category_ids);
                    if ($key == count($category_ids) - 1) {
                        // Last
                        $category_id = 0;
                        $end = 1;
                    } else {
                        $category_id = $category_ids[$key + 1];
                    }
                }
                /* Set Return Value End */
            }
            // Checking Category ID and Category IDs End
            else {
                $category_id = 0;
                $bypass_meal = 1;
                $end = 1;
            }
        }

        //sleep(5);

        $items = collect($items)->map(function ($item) {
            return collect($item)->except([
                // 'order_ids',
                'substitute_ids',
                'production_group_id',
                'updated_at',
                'created_at',
                'created_at_local',
                // 'active_orders',
                // 'active_orders_price',
                // 'lifetime_orders',
                'featured_image'
            ]);
        });

        $meals = collect($meals)->map(function ($meal) {
            return collect($meal)->except([
                // 'order_ids',
                'substitute_ids',
                'production_group_id',
                'updated_at',
                'created_at',
                'created_at_local',
                // 'active_orders',
                // 'active_orders_price',
                // 'lifetime_orders',
                'ingredients',
                // 'ingredient_ids',
                'nutrition',
                'allergy_titles',
                'nutrition',
                'substitute',
                'gallery'
            ]);
        });

        return [
            'items' => $items,
            'meals' => $meals,
            'packages' => $packages,
            'gift_cards' => $giftCards,
            'category_data' => $category_data,
            'offset_meal' => $offset_meal,
            'offset_package' => $offset_package,
            'category_id' => $category_id,
            'bypass_meal' => $bypass_meal,
            'category_ids_str' => $category_ids_str,
            'end' => $end
        ];
    }

    public function refresh_inactive_meals(Request $request)
    {
        $user = auth('api')->user();
        if ($user && $user->hasRole('store') && $user->has('store')) {
            $store_id = $user->store->id;
        } else {
            if (defined('STORE_ID')) {
                $store_id = (int) STORE_ID;
            } else {
                if ($user && isset($user->last_viewed_store)) {
                    $store_id = (int) $user->last_viewed_store->id;
                }
            }
        }
        $meals = Meal::where(['store_id' => $store_id, 'active' => 0])
            ->with(['sizes'])
            ->get();
        return ['meals' => $meals];
    }

    public function refresh_inactive_meal_ids(Request $request)
    {
        $user = auth('api')->user();
        if ($user && $user->hasRole('store') && $user->has('store')) {
            $store_id = $user->store->id;
        } else {
            if (defined('STORE_ID')) {
                $store_id = (int) STORE_ID;
            } else {
                if ($user && isset($user->last_viewed_store)) {
                    $store_id = (int) $user->last_viewed_store->id;
                }
            }
        }
        $meals = Meal::where(['store_id' => $store_id, 'active' => 0])
            ->with(['sizes'])
            ->get()
            ->map(function ($meal) {
                return $meal->id;
            });
        return $meals->toArray();
    }

    public function refresh_inactive_meal_package_ids(Request $request)
    {
        $user = auth('api')->user();
        if ($user && $user->hasRole('store') && $user->has('store')) {
            $store_id = $user->store->id;
        } else {
            if (defined('STORE_ID')) {
                $store_id = (int) STORE_ID;
            } else {
                if ($user && isset($user->last_viewed_store)) {
                    $store_id = (int) $user->last_viewed_store->id;
                }
            }
        }
        $mealPackages = MealPackage::where([
            'store_id' => $store_id,
            'active' => 0
        ])
            ->get()
            ->map(function ($mealPackage) {
                return $mealPackage->id;
            });
        return $mealPackages->toArray();
    }

    public function delivery_days(Request $request)
    {
        $store_id = 0;
        $base_day = '';

        $params = $request->all();
        extract($params);

        $store_id = (int) $store_id;

        $delivery_days = new Collection();
        if ($store_id != 0 && $base_day != "") {
            $delivery_days = DeliveryDay::where('store_id', $store_id)->get();

            if ($delivery_days) {
                foreach ($delivery_days as &$delivery_day) {
                    $delivery_day_meals = DeliveryDayMeal::has(
                        'meal.categories'
                    )
                        ->where('delivery_day_id', $delivery_day->id)
                        ->first();

                    $delivery_day_meal_packages = DeliveryDayMealPackage::has(
                        'meal_package.categories'
                    )
                        ->where('delivery_day_id', $delivery_day->id)
                        ->first();

                    if ($delivery_day_meals || $delivery_day_meal_packages) {
                        $delivery_day->has_items = true;
                    } else {
                        $delivery_day->has_items = false;
                    }
                }
            }
        }

        return [
            'delivery_days' => DeliveryDayResource::collection($delivery_days)
        ];
    }

    public function refresh(Request $request)
    {
        $user = auth('api')->user();
        $store = null;

        if (defined('STORE_ID')) {
            /*$store = Store::with([
                'meals',
                'packages',
                'meals.categories',
                'meals.allergies',
                'packages.meals',
                'packages.sizes',
                'packages.components',
                'packages.addons',
                'meals.sizes',
                'meals.components',
                'meals.addons',
                'meals.macros'
            ])->find(STORE_ID);*/

            $store = Store::find(STORE_ID);
            if ($store) {
                $store_id = (int) $store->id;

                $meals = OptimizedMeal::select(
                    'id',
                    'active',
                    'store_id',
                    'title',
                    'description',
                    'price',
                    'created_at',
                    'default_size_title'
                )
                    ->with(['sizes', 'macros', 'tags'])
                    ->where('store_id', $store_id)
                    ->orderBy('title')
                    ->get();

                $packages = OptimizedMealPackage::select(
                    'id',
                    'store_id',
                    'active',
                    'created_at',
                    'title',
                    'default_size_title',
                    'description',
                    'price'
                )
                    ->with(['sizes'])
                    ->where('store_id', $store_id)
                    ->orderBy('title')
                    ->get();

                $store->meals = $meals;
                $store->packages = $packages;
            }

            return [
                'store' => $store
            ];
        } else {
            $store = null;
            if ($user && isset($user->last_viewed_store)) {
                $store = $user->last_viewed_store;
            }
        }

        return [
            'store' => $store
        ];
    }

    public function refreshMealBag($meal_id)
    {
        // Refresh for Bag
        $meal = OptimizedMeal::select(
            'id',
            'active',
            'store_id',
            'title',
            'description',
            'price',
            'created_at',
            'default_size_title'
        )
            ->with(['sizes', 'macros', 'tags', 'components', 'addons'])
            ->find($meal_id);

        return [
            'meal' => $meal
        ];
    }

    public function refreshMeal($meal_id)
    {
        // Full Refresh
        $meal = Meal::with([
            'categories',
            'allergies',
            'sizes',
            'components',
            'addons',
            'macros'
        ])->find($meal_id);

        $meal['nutrition'] = Meal::getNutrition($meal_id);
        $meal['ingredients'] = Meal::getIngredients($meal_id);

        return [
            'meal' => $meal
        ];
    }

    public function refreshMealIngredients($meal_id)
    {
        $nutrition = Meal::getNutrition($meal_id);
        $ingredients = Meal::getIngredients($meal_id);

        return [
            'nutrition' => $nutrition,
            'ingredients' => $ingredients
        ];
    }

    public function refreshMealByTitle(Request $request)
    {
        $title = str_replace('-', ' ', $request->get('meal_title'));
        // Full Refresh
        $meal = Meal::with([
            'categories',
            'allergies',
            'sizes',
            'components',
            'addons',
            'macros'
        ])
            ->where([
                'title' => $title,
                'store_id' => $request->get('store_id')
            ])
            ->first();

        return [
            'meal' => $meal
        ];
    }

    public function addViewToMeal(Request $request)
    {
        $meal = Meal::where([
            'id' => $request->get('meal_id'),
            'store_id' => $request->get('store_id')
        ])->first();

        if ($meal) {
            $meal->views += 1;
            $meal->update();
        }
    }

    public function refreshMealPackageBag($meal_package_id)
    {
        // Refresh for Bag
        $package = OptimizedMealPackage::select(
            'id',
            'store_id',
            'title',
            'default_size_title',
            'description',
            'price',
            'created_at',
            'updated_at',
            'active'
        )
            ->with(['sizes', 'components', 'addons', 'meals'])
            ->find($meal_package_id);

        return [
            'package' => $package
        ];
    }

    public function refreshMealPackage($meal_package_id)
    {
        $sizesTitles = MealPackageSize::where(
            'meal_package_id',
            $meal_package_id
        )
            ->get()
            ->map(function ($mealPackageSize) {
                return [
                    'id' => $mealPackageSize->id,
                    'title' => $mealPackageSize->title
                ];
            });

        $package = MealPackage::where('id', $meal_package_id)
            ->with([
                'meals',
                'sizes' => function ($query) {
                    $query->where('id', null);
                },
                'sizes.meals',
                'components' => function ($query) {
                    $query->whereHas('options', function ($q) {
                        $q->where('meal_package_size_id', null);
                    });
                },
                'addons' => function ($query) {
                    $query->where('meal_package_size_id', null);
                }
            ])
            ->first();

        if ($package) {
            $package->sizesTitles = $sizesTitles;
        }

        return [
            'package' => $package
        ];
    }

    public function refreshPackageByTitle(Request $request)
    {
        $title = str_replace('-', ' ', $request->get('package_title'));

        // Full Refresh
        $package = MealPackage::where([
            'title' => $title,
            'store_id' => $request->get('store_id')
        ])
            ->with([
                'meals',
                'sizes' => function ($query) {
                    $query->where('id', null);
                },
                'sizes.meals',
                'components' => function ($query) {
                    $query->whereHas('options', function ($q) {
                        $q->where('meal_package_size_id', null);
                    });
                },
                'addons' => function ($query) {
                    $query->where('meal_package_size_id', null);
                }
            ])
            ->first();

        $title = str_replace('-', ' ', $request->get('package_size_title'));

        $packageSize = MealPackageSize::where([
            'title' => $title,
            'store_id' => $request->get('store_id')
        ])->first();

        return [
            'package' => $package,
            'packageSize' => $packageSize
        ];
    }

    public function refreshMealPackageWithSize($meal_package_size_id)
    {
        $packageId = MealPackageSize::where('id', $meal_package_size_id)
            ->pluck('meal_package_id')
            ->first();

        $sizesTitles = MealPackageSize::where('meal_package_id', $packageId)
            ->get()
            ->map(function ($mealPackageSize) {
                return [
                    'id' => $mealPackageSize->id,
                    'title' => $mealPackageSize->title
                ];
            });

        $package = MealPackage::where('id', $packageId)
            ->with([
                'sizes' => function ($query) use ($meal_package_size_id) {
                    $query->where('id', $meal_package_size_id);
                },
                'sizes.meals',
                'components' => function ($query) use ($meal_package_size_id) {
                    $query->whereHas('options', function ($q) use (
                        $meal_package_size_id
                    ) {
                        $q->where(
                            'meal_package_size_id',
                            $meal_package_size_id
                        );
                    });
                },
                'addons' => function ($query) use ($meal_package_size_id) {
                    $query->where(
                        'meal_package_size_id',
                        $meal_package_size_id
                    );
                }
            ])
            ->first();

        $packageSize = MealPackageSize::where('id', $meal_package_size_id)
            ->with(['meals'])
            ->first();

        $package->sizesTitles = $sizesTitles;

        return [
            'package' => $package,
            'package_size' => $packageSize
        ];
    }

    public function getViewedStore()
    {
        $user = auth('api')->user();

        $store = defined('STORE_ID')
            ? Store::with([
                'meals',
                'packages',
                'packages.meals',
                'packages.sizes',
                'packages.components',
                'packages.addons',
                'units',
                'categories',
                'meals.sizes',
                'meals.categories',
                'meals.allergies',
                'meals.components',
                'meals.addons',
                'meals.macros',
                'settings',
                'details',
                'coupons',
                'giftCards',
                'purchasedGiftCards',
                'referrals',
                'referralSettings',
                'promotions',
                'pickupLocations',
                'lineItems',
                'modules',
                'moduleSettings',
                'reportSettings',
                'smsSettings',
                'deliveryFeeZipCodes',
                'deliveryFeeRanges',
                'holidayTransferTimes',
                'menuSettings'
            ])
                ->without([])
                ->find(STORE_ID)
            : null;

        if ($user) {
            $distance = $user->distanceFrom($store);
        } else {
            $distance = 1;
        }

        if ($user && $store) {
            if ($store->settings->delivery_distance_type === 'radius') {
                $willDeliver =
                    $distance < $store->settings->delivery_distance_radius;
            } else {
                $willDeliver = $store->deliversToZip($user->userDetail->zip);
            }
        } else {
            $willDeliver = false;
        }

        return [
            'store' => $store,
            'will_deliver' => $willDeliver,
            'distance' => $distance
        ];
    }
}
