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
        $host = $request->getHost();
        $hostParts = [];
        preg_match('/(.+)\.'.config('app.domain').'/i', $host, $hostParts);

        $slug = count($hostParts) > 1 ? $hostParts[1] : null;

        if ($slug) {
            define('STORE_SLUG', $slug);

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


        $user = auth()->user();
        if($user && $user->hasRole('store') && $user->has('store')) {
          if(!$request->wantsJson() && $user->store->id !== STORE_ID) {
            return redirect()->intended($user->store->getUrl($request->path, $request->secure));
          }
        }

        $request->route()->forgetParameter('store_slug');

        return $next($request);
    }
}