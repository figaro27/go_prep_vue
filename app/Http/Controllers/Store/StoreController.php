<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;

class StoreController extends Controller
{
    protected $store;

    public function __constructor() {
      parent::__constructor();

      $this->store = \Auth::user()->store;
    }
}
