<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Store;

class StoreController extends UserController
{

    public function meals(Request $request, $storeId = null) {
      if($storeId) {
        $store = Store::with(['meals'])->find(STORE_ID);
      }
      else $store = $this->store;

      return $store->meals;
    }
}
