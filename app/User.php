<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Auth;
use Carbon;

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

    public function userPayment(){
        return $this->hasMany('App\UserPayment');
    }

    public function getCreatedAtAttribute($date){
        return Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('M d Y');
    }

// Admin Methods

    public static function getCustomers(){
        return User::with('userPayment', 'userDetail')->get()->map(function($user){
            return [
                      "id" => $user->id,
                      "Name" => $user->userDetail->firstname .' '. $user->userDetail->lastname,
                      "phone" => $user->userDetail->phone,
                      "address" => $user->userDetail->address,
                      "city" => $user->userDetail->city,
                      "state" => $user->userDetail->state,
                      "Joined" => $user->created_at,
                      "LastOrder" => $user->userPayment->max("created_at"),
                      "TotalPayments" => $user->userPayment->count(),
                      "TotalPaid" => $user->userPayment->sum("amount")
            ];                         
        });
    }

    public static function getCustomer($id){
        return User::with('userPayment', 'userDetail')->where('id', $id)->first();
    }



// Store Methods
    
    public static function getStoreCustomers(){
          $id = Auth::user()->id;
          $customers = Order::all()->unique('user_id')->where('store_id', $id)->pluck('user_id');
          return User::with('userDetail', 'userPayment')->whereIn('id', $customers)->get()->map(function($user){
            return [
                  "id" => $user->id,
                  "Name" => $user->userDetail->firstname .' '. $user->userDetail->lastname,
                  "phone" => $user->userDetail->phone,
                  "address" => $user->userDetail->address,
                  "city" => $user->userDetail->city,
                  "state" => $user->userDetail->state,
                  "Joined" => $user->created_at,
                  "LastOrder" => $user->userPayment->max("created_at"),
                  "TotalPayments" => $user->userPayment->count(),
                  "TotalPaid" => $user->userPayment->sum("amount")
            ];
          });
    }


}
