<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Http\Request;

class StoreController extends Controller
{
    protected $store;
    protected $storeName;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            if ($request->wantsJson()) {
                $user = auth('api')->user();
            } else {
                $user = auth()->user();
            }

            if ($user && $user->has('store')) {
                $this->store = $user->store;
            }

            return $next($request);
        });

    }

}
