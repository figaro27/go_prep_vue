<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{

	protected $fillable = [
        'minimum', 'showNutrition', 'allowPickup', 'pickupInstructions', 'applyDeliveryFee', 'deliveryFee'
    ];


    public function store(){
		return $this->belongsTo('App\Store');
	}

	protected $casts = [
        'showNutrition' => 'boolean',
        'applyDeliveryFee' => 'boolean',
        'allowPickup' => 'boolean',
        'delivery_days' => 'json',
        //'cutoff_day' => 'datetime:D',
        //'cutoff_time' => 'datetime:H:i',
        'delivery_distance_zipcodes' => 'json',
    ];
}
