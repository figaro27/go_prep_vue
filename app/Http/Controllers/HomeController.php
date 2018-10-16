<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if(auth()->user()->role == 2){
        return view('store');
        }
        elseif(auth()->user()->role == 3){
        return view('admin');
        }
        return view('customer');
        //return view('home');
    }

}
