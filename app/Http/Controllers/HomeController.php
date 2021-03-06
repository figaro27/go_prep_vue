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
      return view('app');

        if(auth()->user()->user_role_id == 2){
          return view('store');
        }
        elseif(auth()->user()->user_role_id == 3){
          return view('admin');
        }
        return view('customer');
    }

}
