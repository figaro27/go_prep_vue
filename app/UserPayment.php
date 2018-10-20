<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPayment extends Model
{
 //    public function user(){
	// 	return $this->belongsTo('App\User');
	// }

	public function order(){
		return $this->belongsTo('App\Order');
	}
}
