<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function user(){
		return $this->belongsTo('App\User');
	}

	public function store(){
		return $this->belongsTo('App\Store');
	}

	public function userPayment(){
		return $this->hasOne('App\UserPayment');
	}

	public function deliveryStatus(){
		return $this->hasOne('App\DeliveryStatus');
	}
}
