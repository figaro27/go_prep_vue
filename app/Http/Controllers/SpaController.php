<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SpaController extends Controller
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



    public function index()
    {

        if(auth()->user()->user_role_id == 2){
        return view('store');
        }
        elseif(auth()->user()->user_role_id == 3){
        return view('admin');
        }
        return view('customer');
    }
}
