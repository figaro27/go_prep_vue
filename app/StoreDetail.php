<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreDetail extends Model
{
	protected $fillable = [
        'logo', 'name', 'phone', 'address', 'city', 'state', 'zip', 'description'
    ];

    public function store(){
		return $this->belongsTo('App\Store');
	}
}
