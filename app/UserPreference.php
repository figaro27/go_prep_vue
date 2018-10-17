<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    public function user(){
		return $this->belongsTo('App\User');
	}
}
