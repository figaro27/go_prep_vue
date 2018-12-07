<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $fillable = [
        'fulfilled',
    ];

    public function user(){
		return $this->belongsTo('App\User');
	}

	public function store(){
		return $this->belongsTo('App\Store');
	}

	public function meal_orders(){
		return $this->hasMany('App\MealOrder');
	}

}
