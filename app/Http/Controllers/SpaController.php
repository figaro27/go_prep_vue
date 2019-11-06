<?php
namespace App\Http\Controllers;

use App\Allergy;
use App\MealTag;
use App\Store;
use App\Meal;
use App\OptimizedMeal;
use App\OptimizedMealPackage;
use App\MealPackage;
use App\Category;
use Illuminate\Http\Request;

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
                        'coupons',
                        'pickupLocations',
                        'lineItems',
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
                    ])->find(STORE_ID)
                    : null;

                return [
                    'context' => $context,
                    'user' => null,
                    'store' => $store,
                    'allergies' => Allergy::all(),
                    'tags' => MealTag::all()
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
                        'pickupLocations',
                        'productionGroups',
                        'lineItems',
                        'modules',
                        'moduleSettings'
                    ])
                    ->first();

                $user->storeOwner = true;

                return [
                    'context' => $context,
                    'user' => $user,
                    'store' => $store,
                    'allergies' => Allergy::all(),
                    'tags' => MealTag::all()
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
                        'coupons',
                        'pickupLocations',
                        'lineItems',
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

                    return [
                        'context' => $context,
                        'user' => $user,
                        'store' => $store,
                        'store_distance' => $distance ?? null,
                        'will_deliver' => $willDeliver,
                        'allergies' => Allergy::all(),
                        'tags' => MealTag::all()
                    ];
                } else {
                    return [
                        'context' => $context,
                        'user' => $user,
                        'store' => null,
                        'allergies' => Allergy::all(),
                        'tags' => MealTag::all()
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
                    'pickupLocations',
                    'productionGroups',
                    'lineItems',
                    'modules',
                    'moduleSettings'
                ])
                ->first();

            $user->storeOwner = true;

            return [
                'context' => $context,
                'user' => $user,
                'store' => $store,
                'allergies' => Allergy::all(),
                'tags' => MealTag::all()
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
                    'coupons',
                    'pickupLocations',
                    'lineItems'
                ])->find(STORE_ID)
                : $last_viewed_store;

            if ($store && $user) {
                $distance = $user->distanceFrom($store);

                if ($store->settings->delivery_distance_type === 'radius') {
                    ///$distance = $user->distanceFrom($store);
                    $willDeliver =
                        $distance < $store->settings->delivery_distance_radius;
                } else {
                    $willDeliver = $store->deliversToZip(
                        $user->userDetail->zip
                    );
                }

                $user->last_viewed_store_id = $store->id;
                $user->save();

                return [
                    'context' => $context,
                    'user' => $user,
                    'store' => $store,
                    'store_distance' => $distance ?? null,
                    'will_deliver' => $willDeliver,
                    'allergies' => Allergy::all(),
                    'tags' => MealTag::all()
                ];
            } else {
                return [
                    'context' => $context,
                    'user' => $user,
                    'store' => $store,
                    'allergies' => Allergy::all(),
                    'tags' => MealTag::all()
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

    public function refresh_lazy(Request $request)
    {
        // Speed Optimization
        $user = auth('api')->user();

        $store_id = $offset_meal = $offset_package = 0;
        $limit = 30;

        $category_id = 0;
        $category_ids_str = ""; // Full Category Ids

        $bypass_meal = 0;

        $data = $request->all();
        extract($data);

        $offset_meal = (int) $offset_meal;
        $offset_package = (int) $offset_package;
        $category_id = (int) $category_id;
        $category_ids =
            trim($category_ids_str) == ""
                ? []
                : explode(",", trim($category_ids_str));

        $category_data = null;

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

        $items = $meals = $packages = []; // Both of meals and packages
        $end = 0;

        if ($store_id != 0) {
            /* Building Categories */
            if ($category_id == 0 || count($category_ids) == 0) {
                $categories = Category::select(
                    'store_id',
                    'id',
                    'category',
                    'order',
                    'date_range',
                    'date_range_exclusive',
                    'date_range_from',
                    'date_range_to'
                )
                    ->where('store_id', $store_id)
                    ->orderBy('order')
                    ->get();

                if ($categories && count($categories) > 0) {
                    foreach ($categories as $category) {
                        $temp_id = (int) $category->id;

                        $temp_meal = Meal::whereHas('categories', function (
                            $query
                        ) use ($temp_id) {
                            $query->where('categories.id', $temp_id);
                        })
                            ->where('store_id', $store_id)
                            ->first();

                        $temp_package = OptimizedMealPackage::whereHas(
                            'categories',
                            function ($query) use ($temp_id) {
                                $query->where('categories.id', $temp_id);
                            }
                        )
                            ->where('store_id', $store_id)
                            ->first();

                        if ($temp_meal || $temp_package) {
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
                if ($bypass_meal == 0) {
                    $meals = Meal::with([
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
                    $packages = OptimizedMealPackage::with(['sizes'])
                        ->whereHas('categories', function ($query) use (
                            $category_id
                        ) {
                            $query->where('categories.id', $category_id);
                        })
                        ->where('store_id', $store_id)
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
            } // Checking Category ID and Category IDs End
        }

        return [
            'items' => $items,
            'meals' => $meals,
            'packages' => $packages,
            'category_data' => $category_data,
            'offset_meal' => $offset_meal,
            'offset_package' => $offset_package,
            'category_id' => $category_id,
            'bypass_meal' => $bypass_meal,
            'category_ids_str' => $category_ids_str,
            'end' => $end
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

        return [
            'meal' => $meal
        ];
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
        // Full Refresh
        $package = MealPackage::with([
            'meals',
            'sizes',
            'sizes.meals',
            'components',
            'addons'
        ])->find($meal_package_id);

        return [
            'package' => $package
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
                'pickupLocations',
                'lineItems',
                'modules',
                'moduleSettings'
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
