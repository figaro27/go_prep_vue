<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\StoreDetail;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!($token = auth()->attempt($credentials))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = auth('api')->user();
        $secure = Request::secure();
        $storeDomain = StoreDetail::where(
            'store_id',
            $user->last_viewed_store_id
        )
            ->pluck('domain')
            ->first();

        $storeHost = StoreDetail::where('store_id', $user->last_viewed_store_id)
            ->pluck('host')
            ->first();

        $preg =
            '/https?:\/\/(?:www\.)?' . preg_quote(config('app.domain')) . '/i';
        $url = Request::url();

        // If not accessing store subdomain

        if (preg_match($preg, $url)) {
            if ($storeDomain) {
                $end =
                    env('APP_ENV') == 'production' ? '.com' : '.localhost:8000';
                $host = $storeHost ? $storeHost : config('app.domain');
                $redirect = $user->hasRole('store')
                    ? $user->store->getUrl('/store/orders', $secure)
                    : 'http://' . $storeDomain . '.' . $host . '/customer/menu';
            } else {
                $redirect = $user->hasRole('store')
                    ? $user->store->getUrl('/store/orders', $secure)
                    : config('app.front_url');
            }
        } else {
            $redirect = $user->hasRole('store')
                ? $user->store->getUrl('/store/orders', $secure)
                : '/customer/menu';
        }

        return response()->json([
            'access_token' => $token,
            'user' => auth('api')->user(),
            'token_type' => 'bearer',
            'expires_in' =>
                auth()
                    ->factory()
                    ->getTTL() * 60,
            'redirect' => $redirect
        ]);
    }
}
