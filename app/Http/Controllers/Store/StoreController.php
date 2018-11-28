<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;

class StoreController extends Controller
{
    protected $store;
    protected $storeName;

    public function __construct()
    {
      $user = \Auth::user();
      if(isset($user->store)) {
        $this->store = $user->store;
      }
      
    }
}
