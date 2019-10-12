<?php

namespace App\Http\Controllers\User;

use App\Store;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;

class UserController extends Controller
{
    use ValidatesRequests;

    protected $store;
    protected $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (defined('STORE_ID')) {
                $this->store = Store::with(['meals', 'settings'])->find(
                    STORE_ID
                );
            }

            $this->user = auth('api')->user();

            return $next($request);
        });
    }

    public function index()
    {
        return $this->user;
    }

    public function getCustomer()
    {
        return $this->user->customer;
    }
}
