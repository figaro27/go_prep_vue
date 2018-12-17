<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{

	protected $fillable = [
        'firstname', 'lastname', 'address', 'city', 'state'
    ];

    public function user(){
		return $this->belongsTo('App\User');
	}

	public function getFullNameAttribute(){
    	return "{$this->firstname} {$this->lastname}";
    }

    protected $appends = ['full_name'];

}
