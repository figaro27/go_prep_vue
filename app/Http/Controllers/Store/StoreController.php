<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Http\Request;
use App\Ingredient;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    protected $store;
    protected $storeName;

    public function __construct()
    {
        $user = auth('api')->user();

        if ($user && $user->has('store')) {
            $this->store = $user->store;
        }
    }

}
