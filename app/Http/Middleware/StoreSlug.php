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
        $user = auth()->user();
        $host = $request->getHost();
        $hostParts = [];
        preg_match('/(.+)\.' . config('app.domain') . '/i', $host, $hostParts);

        $slug = count($hostParts) > 1 ? $hostParts[1] : null;
        $storeId = $request->headers->get('x-viewed-store-id', null);
        $lastStoreId = $user ? $user->last_viewed_store_id : null;
        $store = null;

        if ($slug) {
            $store = Store::with('storeDetail')
                ->whereHas('storeDetail', function ($query) use ($slug) {
                    return $query->where('domain', $slug);
                })
                ->first();
        } elseif ($storeId) {
            $store = Store::with('storeDetail')->find($storeId);
        } elseif ($lastStoreId) {
            $store = Store::with('storeDetail')->find($lastStoreId);
        }

        if ($store) {
            define('STORE_ID', $store->id);
            define('STORE_SLUG', $store->details->domain);
        } else {
            define('STORE_ID', null);
            define('STORE_SLUG', null);
        }

        if ($user && $user->hasRole('store') && $user->has('store')) {
            if (!$request->wantsJson() && $user->store->id !== STORE_ID) {
                return redirect()->intended(
                    $user->store->getUrl($request->path, $request->secure)
                );
            }
        }

        $request->route()->forgetParameter('store_slug');

        return $next($request);
    }
}
