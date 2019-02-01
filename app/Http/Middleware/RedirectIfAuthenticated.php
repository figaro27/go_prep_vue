<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {

            $user = auth()->user();

            if(!$user) {
              $user = auth('api')->user();
            }

            if(!$user) {
              return $next($request);
            }

            if($user->hasRole('store')) {
              return redirect($user->store->getUrl('/store/orders', $request->secure));
            }
            else {
              return redirect('/customer/home');
            }
        }

        return $next($request);
    }
}
