<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealSubscription extends Pivot
{
    protected $table = 'meal_subscriptions';
    protected $appends = ['title', 'unit_price', 'price'];

    public function meals()
    {
        return $this->belongsTo('App\Meal');
    }

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function meal_size()
    {
        return $this->belongsTo('App\MealSize');
    }

    public function subscriptions()
    {
        return $this->hasMany('App\Subscription');
    }

    public function subscription()
    {
        return $this->belongsTo('App\Subscription');
    }

    public function components()
    {
        return $this->hasMany(
            'App\MealSubscriptionComponent',
            'meal_subscription_id',
            'id'
        );
    }

    public function getTitleAttribute()
    {
        $title = $this->meal->title;

        if ($this->meal_size_id) {
            $title = $this->meal_size->full_title;
        }
        if (count($this->components)) {
            $comp = $this->components
                ->map(function ($component) {
                    return $component->option->title;
                })
                ->implode(', ');
            $title .= ' - ' . $comp;
        }
        return $title;
    }

    public function getUnitPriceAttribute()
    {
        $price = $this->meal->price;

        if ($this->meal->has('sizes') && $this->meal_size_id) {
            $price = $this->meal_size->price;
        }

        if ($this->meal->has('components') && $this->components) {
            foreach ($this->components as $component) {
                $price += $component->option->price;
            }
        }

        return $price;
    }

    public function getPriceAttribute()
    {
        return $this->unit_price * $this->quantity;
    }
}
