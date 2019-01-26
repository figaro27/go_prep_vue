<?php

namespace App\Http\Controllers;

use App\Store;
use Illuminate\Http\Request;
use Auth;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Store::without(['customers'])->get();
    }
}
