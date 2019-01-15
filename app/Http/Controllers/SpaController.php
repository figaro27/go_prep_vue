<?php
namespace App\Http\Controllers;

use App\Store;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Http\Request;
use App\Allergy;

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
            //$this->middleware('view.api');

            $store = defined('STORE_ID') ? Store::with(['meals', 'units', 'categories', 'meals.categories', 'meals.allergies', 'settings'])->find(STORE_ID) : null;

            $user = auth('api')->user();

            $willDeliver = false;

            if ($user) {
                // Store user
                if ($user->hasRole('store') && $user->has('store')) {
                    $store = $user->store()->with(['meals', 'categories', 'meals.categories', 'meals.allergies', 'units', 'settings', 'storeDetail'])->first();
                }

                // Customer user
                if($user->hasRole('customer')) {
                  if($store->settings->delivery_distance_type === 'radius') {
                    $distance = $user->distanceFrom($store);
                    $willDeliver = $distance < $store->settings->delivery_distance_radius;
                  }
                  else {
                    $willDeliver = $store->deliversToZip($user->userDetail->zip);
                  }
                }
            }
            // Not logged in
            else {
              
            }

            if ($store) {
                $orderIngredients = Cache::remember('order_ingredients_' . $store->id, 10, function () use ($store) {
                    return $store->getOrderIngredients();
                });
            }

            if ($user) {
                if ($user->has('store')) {
                    $orders = $user->store->orders()->with(['user', 'user.userDetail', 'meals'])->get();
                }
            }

            $stores = Cache::remember('stores', 10, function () {
                return Store::all();
            });

            return [
                'user' => $user ?? null,
                'store' => $store,
                'will_deliver' => $willDeliver,
                'stores' => $stores,
                'order_ingredients' => $orderIngredients ?? [],
                'allergies' => Allergy::all(),
                'orders' => $orders ?? [],
            ];

        } else {
            $user = auth()->user();

            if ($user) {
                if ($user->user_role_id === 2) {
                    return view('store');
                } elseif ($user->user_role_id === 3) {
                    return view('admin');
                }
                else {
                  return view('customer');
                }
            }
            
            return redirect('/login');

        }

    }
}
