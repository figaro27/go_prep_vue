<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function user(){
		return $this->belongsTo('App\User');
	}

	public function userPaymentMethod(){
		return $this->hasOne('App\UserPaymentMethod');
	}

	public function store(){
		return $this->belongsTo('App\Store');
	}

	public function deliveryStatus(){
		return $this->hasOne('App\DeliveryStatus');
	}
}
