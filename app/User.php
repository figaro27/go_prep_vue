<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Auth;
use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

     public function userRole(){
        return $this->hasOne('App\UserRole');
    }

    public function userDetail(){
        return $this->hasOne('App\UserDetail');
    }

    public function order(){
        return $this->hasMany('App\Order');
    }


// Admin Methods

    public static function getCustomers(){
        return User::with('userDetail', 'order')->where('user_role_id', '=', 1)->get()->map(function($user){
            return [
                      "id" => $user->id,
                      "user_role_id" => $user->user_role_id,
                      "Name" => $user->userDetail->firstname .' '. $user->userDetail->lastname,
                      "phone" => $user->userDetail->phone,
                      "address" => $user->userDetail->address,
                      "city" => $user->userDetail->city,
                      "state" => $user->userDetail->state,
                      "Joined" => $user->created_at->format('m-d-Y'),
                      "LastOrder" =>  optional($user->order->max("created_at"))->format('m-d-Y'),
                      "TotalPayments" => $user->order->count(),
                      "TotalPaid" => '$'.number_format($user->order->sum("amount"), 2, '.', ',')
            ];                         
        });
    }

    public static function getCustomer($id){
        return User::with('userDetail', 'order')->where('id', $id)->first();
    }



// Store Methods
    
    public static function getStoreCustomers(){
          $id = Auth::user()->id;
          $customers = Order::all()->unique('user_id')->where('store_id', $id)->pluck('user_id');
          return User::with('userDetail', 'order')->whereIn('id', $customers)->get()->map(function($user){
            return [
                  "id" => $user->id,
                  "Name" => $user->userDetail->firstname .' '. $user->userDetail->lastname,
                  "phone" => $user->userDetail->phone,
                  "address" => $user->userDetail->address,
                  "city" => $user->userDetail->city,
                  "state" => $user->userDetail->state,
                  "Joined" => $user->created_at->format('m-d-Y'),
                  "LastOrder" => $user->order->max("created_at")->format('m-d-Y'),
                  "TotalPayments" => $user->order->count(),
                  "TotalPaid" => '$'.number_format($user->order->sum("amount"), 2, '.', ',')
            ];
          });
    }


}
