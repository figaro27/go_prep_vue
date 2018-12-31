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

            if (auth()->user()) {
                if (auth()->user()->user_role_id == 2) {
                    return [];
                } elseif (auth()->user()->user_role_id == 3) {
                    return [];
                }
            }
            
            return [
                'store' => defined('STORE_ID') ? Store::with('meals', 'meals.ingredients', 'meals.mealCategories', 'storeSettings')->find(STORE_ID) : null,
                'stores' => Store::all(),
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
