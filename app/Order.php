<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function user(){
		return $this->belongsTo('App\User');
	}

	public function delivery_status(){
		return $this->hasOne('App\DeliveryStatus');
	}

	public function store(){
		return $this->belongsTo('App\Store');
	}
}
