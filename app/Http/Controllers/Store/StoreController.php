<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    protected $user;
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
                $this->user = $user;
                $this->store = $user->store;

                /* Timezone */
                if (isset($this->store) && isset($this->store->settings)) {
                    $settings = $this->store->settings;
                    if ($settings && $settings->timezone) {
                        $timezone = $settings->timezone;
                        date_default_timezone_set($timezone);
                    }
                }
                /* Timezone End */
            }

            return $next($request);
        });
    }
}
