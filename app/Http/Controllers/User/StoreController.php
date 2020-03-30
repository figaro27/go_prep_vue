<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Store;
use App\Customer;

class StoreController extends UserController
{
    public function index()
    {
        $stores = Store::all();
        return $stores->makeHidden(['settings.notifications']);
    }

    public function meals(Request $request, $storeId = null)
    {
        if ($storeId) {
            $store = Store::with(['meals'])->find(STORE_ID);
        } else {
            $store = $this->store;
        }

        return $store->meals;
    }

    public function getPromotionPoints(Request $request)
    {
        $points = Customer::where([
            'user_id' => $request->get('userId'),
            'store_id' => $request->get('storeId')
        ])
            ->pluck('points')
            ->first();
        return $points;
    }
}
