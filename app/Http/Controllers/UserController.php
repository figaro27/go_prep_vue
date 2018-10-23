<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::getCustomers();
    }

    public function storeIndex(){
        return User::getStoreCustomers();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return User::getCustomer($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // return $request->user['user_detail']['firstname'];

        $user = User::with('userDetail', 'userPayment')->find($id);
       
        // $user->email = $request->user['email'];
        // $user->userDetail->firstname = $request->user['user_detail'];
        $user->userDetail->update([
            'firstname' => $request->user['user_detail']['firstname'],
            'lastname' => $request->user['user_detail']['lastname'],
            'address' => $request->user['user_detail']['address'],
            'city' => $request->user['user_detail']['city'],
            'state' => $request->user['user_detail']['state'],
            'phone' => $request->user['user_detail']['phone'],
            'delivery' => $request->user['user_detail']['delivery'],
        ]);
        // $user->userDetail->firstname = $request->user['user_detail']['firstname'];
        // $user->userDetail->lastname = $request->user->user_detail['lastname'];
        // $user->userDetail->address = $request->user->user_detail['address'];
        // $user->userDetail->city = $request->user->user_detail['city'];
        // $user->userDetail->state = $request->user->user_detail['state'];
        // $user->userDetail->phone = $request->user->user_detail['phone'];
        // $user->userDetail->delivery = $request->user->user_detail['delivery'];
        
        // $user->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
