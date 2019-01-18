<?php

namespace App\Http\Middleware;

use Closure;

class IsCustomer
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
        $user = auth('api')->user();
        
        if ($user && $user->user_role_id === 1) {
            return $next($request);
        }

        if (!$request->expectsJson()) {
            return redirect('home')->with('error', 'You do not have store access');
        }

        return response()->json([
            'error' => 'Not authorized.',
        ], 401);
    }

}
