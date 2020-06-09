<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealSubscription extends Pivot
{
    protected $table = 'meal_subscriptions';
    protected $appends = [
        'title',
        'html_title',
        'fullTitle',
        'short_title',
        'base_size',
        'unit_price'
    ];
    protected $casts = [
        'delivery_date' => 'date:Y-m-d'
    ];

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
        $title = $this->customTitle ? $this->customTitle : $this->meal->title;

        if ($this->meal_size_id && $this->meal_size) {
            $title = $this->meal_size->full_title;
        }
        if (count($this->components) || count($this->addons)) {
            $comp = $this->components
                ->map(function ($component) {
                    return $component->option
                        ? $component->option->title
                        : null;
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
        $title = $this->fullTitle;

        $hasComponents = count($this->components);
        $hasAddons = count($this->addons);

        if ($hasComponents || $hasAddons) {
            $title .= '<ul class="plain mb-0">';

            if ($hasComponents) {
                foreach ($this->components as $component) {
                    if (
                        isset($component->option) &&
                        $component->option != null &&
                        isset($component->option->title)
                    ) {
                        $title .=
                            '<li class="plain">' .
                            $component->option->title .
                            '</li>';
                    }
                }
            }
            if ($hasAddons) {
                foreach ($this->addons as $addon) {
                    $title .=
                        '<li class="plus">' . isset($addon->addon->title) &&
                        $addon->addon != null
                            ? $addon->addon->title
                            : null . '</li>';
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
        return $this->price / $this->quantity;
        // $price = $this->meal->price;

        // if (
        //     $this->meal->has('sizes') &&
        //     $this->meal_size_id &&
        //     $this->meal_size
        // ) {
        //     $price = $this->meal_size->price;
        // }

        // if ($this->meal->has('components') && $this->components) {
        //     foreach ($this->components as $component) {
        //         if (
        //             isset($component->option) &&
        //             $component->option != null &&
        //             isset($component->option->price)
        //         ) {
        //             $price += $component->option->price;
        //         }
        //     }
        // }
        // if ($this->meal->has('addons') && $this->addons) {
        //     foreach ($this->addons as $addon) {
        //         $price += $addon->addon->price;
        //     }
        // }

        // return $price;
    }

    public function getBaseSizeAttribute()
    {
        if ($this->customSize) {
            return $this->customSize;
        }
        if ($this->meal_size_id && $this->meal_size) {
            return $this->meal_size->title;
        } else {
            if ($this->meal->default_size_title != null) {
                return $this->meal->default_size_title;
            }
        }
    }

    public function getShortTitleAttribute()
    {
        return $this->customTitle ? $this->customTitle : $this->meal->title;
    }

    public function getFullTitleAttribute()
    {
        $size = $this->base_size ? ' - ' . $this->base_size : null;
        return $this->short_title . $size;
    }
}
