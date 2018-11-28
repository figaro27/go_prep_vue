<?php

namespace App\Http\Controllers\User;

use App\User;

class UserController extends Controller
{
    protected $store;

    public function __constructor()
    {
        parent::__constructor();

        // Try to find store by subdomain
        //$root = Request::root();
        //$this->storeName = str_replace(['http://', 'https://', config('app.domain')], '', $root);

    }
}
