<?php
namespace App\Http\Controllers;

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
            //$this->middleware('view.api');

            $store = defined('STORE_ID') ? Store::with('meals')->find(STORE_ID) : null;

            $user = auth('api')->user();

            if ($user) {
              /*
                if ($user->role == 2) {
                    return [];
                } elseif ($user->role == 3) {
                    return [];
                }*/

                if($user->has('store')) {
                  $store = $user->store()->with('meals')->first();
                }
            }


            if($store) {
              $orderIngredients = $store->getOrderIngredients();
            }
            
            return [
                'store' => $store,
                'stores' => Store::all(),
                'order_ingredients' => $orderIngredients ?? [],
            ];
        } else {
            if (auth()->user()) {
                if (auth()->user()->user_role_id == 2) {
                    return view('store');
                } elseif (auth()->user()->user_role_id == 3) {
                    return view('admin');
                }
            }
            return view('customer');

        }

    }
}
