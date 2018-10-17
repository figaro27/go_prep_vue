<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPaymentMethod extends Model
{
    public function user(){
		return $this->belongsTo('App\User');
	}
}
