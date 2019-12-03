<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Order;

class OrderController extends UserController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->user
            ->orders()
            ->with(['pickup_location', 'purchased_gift_cards'])
            ->get();
    }
}
