<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{

	protected $fillable = [
        'firstname', 'lastname', 'address'
    ];

    public function user(){
		return $this->belongsTo('App\User');
	}
}
