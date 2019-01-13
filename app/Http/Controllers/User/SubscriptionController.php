<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;

class SubscriptionController extends UserController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->user->subscriptions;
    }
}
