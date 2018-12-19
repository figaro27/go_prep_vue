<?php

namespace App\Http\Controllers\User;

use App\User;
use App\Store;

class UserController
{
    protected $store;

    public function __constructor()
    {
        parent::__constructor();

        if(defined('STORE_ID')) {
          $this->store = Store::with(['meals'])->find(STORE_ID);
        }
    }
}
