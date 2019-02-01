<?php

namespace App\Http\Controllers\User;

use App\Store;
use App\User;
use Illuminate\Foundation\Validation\ValidatesRequests;

class UserController
{
    use ValidatesRequests;

    protected $store;
    protected $user;

    public function __construct()
    {
        if (defined('STORE_ID')) {
            $this->store = Store::with(['meals', 'settings'])->find(STORE_ID);
        }

        $this->user = auth('api')->user();
    }

    public function index()
    {
        return $this->user;
    }
}
