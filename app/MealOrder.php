<?php

namespace App;
use Carbon\Carbon;
use App\MealComponentOption;
use App\MealAddon;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MealOrder extends Pivot
{
    protected $table = 'meal_orders';

    protected $hidden = [];

    protected $casts = [
        'free' => 'boolean',
        'meal_package' => 'boolean',
        'delivery_date' => 'date:Y-m-d'
    ];

    protected $appends = [
        'short_title',
        'title',
        'html_title',
        'base_title_without_date',
        'base_title',
        'base_size',
        'unit_price',
        'instructions',
        'componentsFormat',
        'addonsFormat',
        'expirationDate',
        'ingredientList',
        'allergyList',
        'hasCustomName',
        'fullTitle'
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

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function components()
    {
        return $this->hasMany('App\MealOrderComponent', 'meal_order_id', 'id');
    }

    public function addons()
    {
        return $this->hasMany('App\MealOrderAddon', 'meal_order_id', 'id');
    }

    public function getShortTitleAttribute()
    {
        return $this->customTitle ? $this->customTitle : $this->meal->title;
    }

    public function getBaseTitleAttribute()
    {
        $title = $this->customTitle ? $this->customTitle : $this->meal->title;

        $hasComponents = count($this->components);
        $hasAddons = count($this->addons);

        if ($hasComponents || $hasAddons) {
            $title .= '<ul class="plain mb-0 pb-0">';

            if ($hasComponents) {
                foreach ($this->components as $component) {
                    if (
                        isset($component->option) &&
                        $component->option != null &&
                        isset($component->option->title)
                    ) {
                        $title .=
                            '<li class="plain" style="font-size:14px">' .
                            $component->option->title .
                            '</li>';
                    }
                }
            }
            if ($hasAddons) {
                foreach ($this->addons as $addon) {
                    $title .= '<li class="plus" style="font-size:14px">';
                    if (isset($addon->addon->title) && $addon->addon != null) {
                        $title .= $addon->addon->title;
                    }
                    $title .= '</li>';
                }
            }

            $title .= '</ul>';
        }

        if ($this->special_instructions != null) {
            $title .=
                '<p style="font-size:14px;font-weight:bold">' .
                $this->special_instructions .
                '</p>';
        }

        if (
            //$this->order->store->modules->multipleDeliveryDays &&
            isset($this->order) &&
            isset($this->order->store) &&
            isset($this->order->store->modules) &&
            (int) $this->order->isMultipleDelivery == 1 &&
            $this->delivery_date
        ) {
            $deliveryDate = new Carbon($this->delivery_date);
            if ($this->store->settings->deliveryWeeks > 0) {
                $title = '(' . $deliveryDate->format('l, m/d') . ') ' . $title;
            } else {
                $title = '(' . $deliveryDate->format('l') . ') ' . $title;
            }
        }

        return $title;
    }

    public function getBaseTitleWithoutDateAttribute()
    {
        $title = $this->customTitle ? $this->customTitle : $this->meal->title;

        $hasComponents = count($this->components);
        $hasAddons = count($this->addons);

        if ($hasComponents || $hasAddons) {
            $title .= '<ul class="plain mb-0 pb-0">';

            if ($hasComponents) {
                foreach ($this->components as $component) {
                    if (
                        isset($component->option) &&
                        $component->option != null &&
                        isset($component->option->title)
                    ) {
                        $title .=
                            '<li class="plain" style="font-size:14px">' .
                            $component->option->title .
                            '</li>';
                    }
                }
            }
            if ($hasAddons) {
                foreach ($this->addons as $addon) {
                    $title .= '<li class="plus" style="font-size:14px">';
                    if (isset($addon->addon->title) && $addon->addon != null) {
                        $title .= $addon->addon->title;
                    }
                    $title .= '</li>';
                }
            }

            $title .= '</ul>';
        }

        if ($this->special_instructions != null) {
            $title .=
                '<p style="font-size:14px;font-weight:bold">' .
                $this->special_instructions .
                '</p>';
        }

        if (
            //$this->order->store->modules->multipleDeliveryDays &&
            isset($this->order) &&
            isset($this->order->store) &&
            isset($this->order->store->modules) &&
            (int) $this->order->isMultipleDelivery == 1 &&
            $this->delivery_date
        ) {
            $deliveryDate = new Carbon($this->delivery_date);
        }

        return $title;
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

    public function getTitleAttribute()
    {
        $title = $this->customTitle ? $this->customTitle : $this->meal->title;

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
                        isset($addon->addon->title) &&
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

        if (
            //$this->order->store->modules->multipleDeliveryDays &&
            isset($this->order) &&
            isset($this->order->store) &&
            isset($this->order->store->modules) &&
            (int) $this->order->isMultipleDelivery == 1 &&
            $this->delivery_date
        ) {
            $deliveryDate = new Carbon($this->delivery_date);
            $title = '(' . $deliveryDate->format('D, m/d/Y') . ') ' . $title;
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

        if ($this->hasCustomName) {
            $title = $this->customTitle;
        }

        if (count($this->components)) {
            if (
                isset($component->option) &&
                $component->option != null &&
                isset($component->option->price)
            ) {
                $comp = $this->components
                    ->map(function ($component) {
                        return $component->option->title;
                    })
                    ->implode(', ');
                $title .= ' - ' . $comp;
            }
        }
        if (count($this->addons)) {
            $comp = $this->addons
                ->map(function ($addon) {
                    return isset($addon->addon->title)
                        ? $addon->addon->title
                        : null;
                })
                ->implode(', ');
            $title .= ' - ' . $comp;
        }
        if ($this->special_instructions != null) {
            $title .= $this->special_instructions;
        }

        if (
            //$this->order->store->modules->multipleDeliveryDays &&
            isset($this->order) &&
            isset($this->order->store) &&
            isset($this->order->store->modules) &&
            (int) $this->order->isMultipleDelivery == 1 &&
            $this->delivery_date
        ) {
            $deliveryDate = new Carbon($this->delivery_date);
            $title = '(' . $deliveryDate->format('D, m/d/Y') . ') ' . $title;
        }

        return $title;
    }

    public function getHtmlTitleAttribute()
    {
        $title = $this->fullTitle;

        $hasComponents = count($this->components);
        $hasAddons = count($this->addons);

        if ($hasComponents || $hasAddons) {
            $title .= '<ul class="plain mb-0 pb-0">';

            if ($hasComponents) {
                foreach ($this->components as $component) {
                    if (
                        isset($component->option) &&
                        $component->option != null &&
                        isset($component->option->title)
                    ) {
                        $title .=
                            '<li class="plain" style="font-size:14px">' .
                            $component->option->title .
                            '</li>';
                    }
                }
            }
            if ($hasAddons) {
                foreach ($this->addons as $addon) {
                    $title .= '<li class="plus" style="font-size:14px;">';
                    if (isset($addon->addon->title) && $addon->addon != null) {
                        $title .= $addon->addon->title;
                    }
                    $title .= '</li>';
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

        if (
            // $this->order->store->modules->multipleDeliveryDays &&
            isset($this->order) &&
            isset($this->order->store) &&
            isset($this->order->store->modules) &&
            (int) $this->order->isMultipleDelivery == 1 &&
            $this->delivery_date
        ) {
            $deliveryDate = new Carbon($this->delivery_date);
            $title = '(' . $deliveryDate->format('l, F jS') . ') ' . $title;
        }

        return $title;
    }

    public function getUnitPriceAttribute()
    {
        if ($this->price) {
            return $this->price / $this->quantity;
        }

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
                if (
                    isset($component->option) &&
                    $component->option != null &&
                    isset($component->option->price)
                ) {
                    $price += $component->option->price;
                }
            }
        }
        if ($this->meal->has('addons') && $this->addons) {
            foreach ($this->addons as $addon) {
                $price += isset($addon->addon->title)
                    ? $addon->addon->price
                    : null;
            }
        }

        if ($this->free || $this->meal_package) {
            $price = 0;
        }

        return $price;
    }

    public function getInstructionsAttribute()
    {
        return $this->meal->instructions;
    }

    public function getComponentsFormatAttribute()
    {
        $components = '';
        foreach ($this->components as $component) {
            $components .= MealComponentOption::where(
                'id',
                $component->meal_component_option_id
            )
                ->pluck('title')
                ->first();
            $components .= ', ';
        }
        $components = substr($components, 0, -2);
        return $components;
    }

    public function getAddonsFormatAttribute()
    {
        $addons = '';
        foreach ($this->addons as $addon) {
            $addons .= MealAddon::where('id', $addon->meal_addon_id)
                ->pluck('title')
                ->first();
            $addons .= ', ';
        }
        $addons = substr($addons, 0, -2);
        return $addons;
    }

    public function getExpirationDateAttribute()
    {
        $deliveryDate = new Carbon($this->order->delivery_date);
        $expirationDate = $deliveryDate
            ->addDays($this->meal->expirationDays)
            ->format('m/d/Y');
        return $expirationDate;
    }

    public function getIngredientListAttribute()
    {
        $meal = $this->meal;

        if ($this->meal_size_id) {
            $meal = MealSize::where('id', $this->meal_size_id)->first();
        }

        $mainIngredients = $meal->ingredients;
        $ingredientList = '';

        foreach ($mainIngredients as $ingredient) {
            $ingredientList .= $ingredient['food_name'] . ', ';
        }

        foreach ($this->components as $component) {
            $ingredients = MealComponentOption::where(
                'id',
                $component['meal_component_option_id']
            )
                ->with('ingredients')
                ->first()->ingredients;
            foreach ($ingredients as $ingredient) {
                if (!$ingredient->attributes['hidden']) {
                    $ingredientList .= $ingredient['food_name'] . ', ';
                }
            }
        }

        foreach ($this->addons as $addon) {
            $ingredients = MealAddon::where('id', $addon['meal_addon_id'])
                ->with('ingredients')
                ->first()->ingredients;
            foreach ($ingredients as $ingredient) {
                if (!$ingredient->attributes['hidden']) {
                    $ingredientList .= $ingredient['food_name'] . ', ';
                }
            }
        }

        return $ingredientList;

        // $ingredients = $this->meal->ingredients;
        // $ingredientList = '';
        // foreach ($ingredients as $ingredient) {
        //     $ingredientList .= $ingredient['food_name'] . ', ';
        // }
        // return $ingredientList;
    }

    public function getAllergyListAttribute()
    {
        $allergyList = '';
        foreach ($this->meal->allergy_titles as $allergy) {
            $allergyList .= $allergy . ', ';
        }
        $allergyList = rtrim($allergyList, ', ');
        return $allergyList;
    }

    public function getHasCustomNameAttribute()
    {
        if ($this->customTitle || $this->customSize) {
            return true;
        } else {
            return false;
        }
    }

    public function getFullTitleAttribute()
    {
        $size = $this->base_size ? ' - ' . $this->base_size : null;
        return $this->short_title . $size;
    }
}
