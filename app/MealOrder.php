<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MealOrder extends Pivot
{
    protected $table = 'meal_orders';

    protected $casts = [
        'free' => 'boolean',
        'meal_package' => 'boolean'
    ];

    protected $appends = [
        'title',
        'html_title',
        'unit_price',
        'price',
        'instructions'
    ];
    protected $with = ['components', 'addons'];

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

    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public function components()
    {
        return $this->hasMany('App\MealOrderComponent', 'meal_order_id', 'id');
    }

    public function addons()
    {
        return $this->hasMany('App\MealOrderAddon', 'meal_order_id', 'id');
    }

    public function getTitleAttribute()
    {
        $title = $this->meal->title;

        if ($this->meal_size_id && $this->meal_size) {
            $title = $this->meal_size->full_title;
        } else {
            if (
                $this->meal->default_size_title != 'Medium' &&
                $this->meal->default_size_title != null
            ) {
                $title =
                    $this->meal->title .
                    ' - ' .
                    $this->meal->default_size_title;
            }
        }

        if (count($this->components)) {
            $comp = $this->components
                ->map(function ($component) {
                    if (
                        isset($component->option) &&
                        $component->option != null &&
                        isset($component->option->title)
                    ) {
                        return $component->option->title;
                    } else {
                        return "";
                    }
                })
                ->implode(', ');
            $title .= ' - ' . $comp;
        }
        if (count($this->addons)) {
            $comp = $this->addons
                ->map(function ($addon) {
                    if (
                        isset($addon->addon) &&
                        $addon->addon != null &&
                        isset($addon->addon->title)
                    ) {
                        return $addon->addon->title;
                    } else {
                        return "";
                    }
                })
                ->implode(', ');
            $title .= ' - ' . $comp;
        }
        if ($this->special_instructions != null) {
            $title .= $this->special_instructions;
        }
        return $title;
    }

    public function getTitle()
    {
        $title = $this->meal->title;

        if ($this->meal_size_id && $this->meal_size) {
            $title = $this->meal_size->full_title;
        } else {
            if (
                $this->meal->default_size_title != 'Medium' &&
                $this->meal->default_size_title != null
            ) {
                $title =
                    $this->meal->title .
                    ' - ' .
                    $this->meal->default_size_title;
            }
        }

        if (count($this->components)) {
            $comp = $this->components
                ->map(function ($component) {
                    return $component->option->title;
                })
                ->implode(', ');
            $title .= ' - ' . $comp;
        }
        if (count($this->addons)) {
            $comp = $this->addons
                ->map(function ($addon) {
                    return $addon->addon->title;
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
        } else {
            if (
                $this->meal->default_size_title != 'Medium' &&
                $this->meal->default_size_title != null
            ) {
                $title =
                    $this->meal->title .
                    ' - ' .
                    $this->meal->default_size_title;
            }
        }

        $hasComponents = count($this->components);
        $hasAddons = count($this->addons);

        if ($hasComponents || $hasAddons) {
            $title .= '<ul class="plain mb-0 pb-0">';

            if ($hasComponents) {
                foreach ($this->components as $component) {
                    $title .=
                        '<li class="plain" style="font-size:14px">' .
                        $component->option->title .
                        '</li>';
                }
            }
            if ($hasAddons) {
                foreach ($this->addons as $addon) {
                    $title .=
                        '<li class="plus" style="font-size:14px;">' .
                        $addon->addon->title .
                        '</li>';
                }
            }

            $title .= '</ul>';
        }

        if ($this->special_instructions != null) {
            $title .=
                '<p style="font-size:10px">' .
                $this->special_instructions .
                '</p>';
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

        if ($this->free || $this->meal_package) {
            $price = 0;
        }

        return $price;
    }

    public function getPriceAttribute()
    {
        return $this->unit_price * $this->quantity;
    }

    public function getInstructionsAttribute()
    {
        return $this->meal->instructions;
    }
}
