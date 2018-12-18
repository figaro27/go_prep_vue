<?php

namespace App\Http\Middleware;

use App\Store;
use Closure;

class StoreSlug
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Route::input('store_slug')) {
            define('STORE_SLUG', \Route::input('store_slug'));

            $store = Store::with('storeDetail')->whereHas('storeDetail', function ($query) {
                return $query->where('domain', STORE_SLUG);
            })->first();

            if ($store) {
                define('STORE_ID', $store->id);
            } else {
                define('STORE_ID', null);
            }

        } else {
            define('STORE_SLUG', null);
            define('STORE_ID', null);
        }

        return $next($request);
    }
}