<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealSubscription extends Pivot
{
    protected $table = 'meal_subscriptions';
    protected $appends = ['title', 'html_title', 'unit_price', 'price'];

    public function meals()
    {
        return $this->belongsTo('App\Meal')->withTrashed();
    }

    public function meal()
    {
        return $this->belongsTo('App\Meal')->withTrashed();
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

    public function addons()
    {
        return $this->hasMany(
            'App\MealSubscriptionAddon',
            'meal_subscription_id',
            'id'
        );
    }

    public function getTitleAttribute()
    {
        $title = $this->meal->title;

        if ($this->meal_size_id && $this->meal_size) {
            $title = $this->meal_size->full_title;
        }
        if (count($this->components) || count($this->addons)) {
            $comp = $this->components
                ->map(function ($component) {
                    return $component->option->title;
                })
                ->implode(', ');

            $comp .= $this->addons
                ->map(function ($addon) {
                    return $addon->title;
                })
                ->implode(', ');

            $title .= ' - ' . $comp;
        }
        if ($this->special_instructions != null) {
            $title .= $this->special_instructions;
        }

        return $title;
    }

    public function getHtmlTitleAttribute()
    {
        $title = $this->meal->title;

        if ($this->meal_size_id && $this->meal_size) {
            $title = $this->meal_size->full_title;
        }

        $hasComponents = count($this->components);
        $hasAddons = count($this->addons);

        if ($hasComponents || $hasAddons) {
            $title .= '<ul class="plain mb-0">';

            if ($hasComponents) {
                foreach ($this->components as $component) {
                    $title .=
                        '<li class="plain">' .
                        $component->option->title .
                        '</li>';
                }
            }
            if ($hasAddons) {
                foreach ($this->addons as $addon) {
                    $title .=
                        '<li class="plus">' . $addon->addon->title . '</li>';
                }
            }

            if ($this->special_instructions != null) {
                $title .=
                    '<p style="font-size:10px">' .
                    $this->special_instructions .
                    '</p>';
            }

            $title .= '</ul>';
        }
        return $title;
    }

    public function getUnitPriceAttribute()
    {
        $price = $this->meal->price;

        if (
            $this->meal->has('sizes') &&
            $this->meal_size_id &&
            $this->meal_size
        ) {
            $price = $this->meal_size->price;
        }

        if ($this->meal->has('components') && $this->components) {
            foreach ($this->components as $component) {
                $price += $component->option->price;
            }
        }
        if ($this->meal->has('addons') && $this->addons) {
            foreach ($this->addons as $addon) {
                $price += $addon->price;
            }
        }

        return $price;
    }

    public function getPriceAttribute()
    {
        return $this->unit_price * $this->quantity;
    }
}
