<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $fillable = [
        'fulfilled', 'notes',
    ];

    protected $casts = [
        'amount' => 'double',
        'created_at' => 'date:F d, Y'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function meal_orders()
    {
        return $this->hasMany('App\MealOrder');
    }

    public function meals()
    {
        return $this->belongsToMany('App\Meal', 'meal_orders');
    }

    public static function updateOrder($id, $props)
    {

        $order = Order::findOrFail($id);

        $props = collect($props)->only([
            'fulfilled',
            'notes',
        ]);

        $order->update($props->toArray());

    }

}
