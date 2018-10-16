<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    public function user(){
		return $this->hasMany('App\User');
	}

	public function storeMeta(){
		return $this->hasMany('App\storeMeta');
	}
}
