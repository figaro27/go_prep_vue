<?php
namespace App\Http\Controllers;

use App\Allergy;
use App\MealTag;
use App\Store;
use \Illuminate\Http\Request;

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
                $store = defined('STORE_ID') ? Store::with([
                    'meals',
                    'units',
                    'categories',
                    'meals.categories',
                    'meals.allergies',
                    'settings',
                ])->find(STORE_ID) : null;

                return [
                    'context' => $context,
                    'user' => null,
                    'store' => $store,
                    'allergies' => Allergy::all(),
                    'tags' => MealTag::all(),
                ];
            } elseif ($context === 'store') {
                $store = $user->store()->with([
                    'categories',
                    'ingredients',
                    'units',
                    'settings',
                    'storeDetail',
                ])->first();

                return [
                    'context' => $context,
                    'user' => $user,
                    'store' => $store,
                    'allergies' => Allergy::all(),
                    'tags' => MealTag::all(),
                ];
            } elseif ($context === 'customer') {
                $store = defined('STORE_ID') ?
                Store::with([
                    'meals',
                    'units',
                    'categories',
                    'meals.categories',
                    'meals.allergies',
                    'settings',
                ])->find(STORE_ID) : null;

                if ($store) {
                    if ($store->settings->delivery_distance_type === 'radius') {
                        $distance = $user->distanceFrom($store);
                        $willDeliver = $distance < $store->settings->delivery_distance_radius;
                    } else {
                        $willDeliver = $store->deliversToZip($user->userDetail->zip);
                    }

                    return [
                        'context' => $context,
                        'user' => $user,
                        'store' => $store,
                        'store_distance' => $distance ?? null,
                        'will_deliver' => $willDeliver,
                        'allergies' => Allergy::all(),
                        'tags' => MealTag::all(),
                    ];
                } else {
                    return [
                        'context' => $context,
                        'user' => $user,
                        'store' => null,
                        'allergies' => Allergy::all(),
                        'tags' => MealTag::all(),
                    ];
                }

            }

        } else {
            $user = auth()->user();
            return view('app');

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

    public function getViewedStore()
    {
        $user = auth('api')->user();

        $store = defined('STORE_ID') ?
        Store::with([
            'meals',
            'units',
            'categories',
            'meals.categories',
            'meals.allergies',
            'settings',
            'details',
        ])->find(STORE_ID) : null;

        if ($user) {
            if ($store->settings->delivery_distance_type === 'radius') {
                $distance = $user->distanceFrom($store);
                $willDeliver = $distance < $store->settings->delivery_distance_radius;
            } else {
                $willDeliver = $store->deliversToZip($user->userDetail->zip);
            }
        }
        else {
            $willDeliver = false;
        }

        return [
            'store' => $store,
            'will_deliver' => $willDeliver,
        ];
    }
}
