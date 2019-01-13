<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;

class OrderController extends UserController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->user->orders;
    }
}
