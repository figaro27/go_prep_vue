<?php

namespace App;

use App\MealAddon;
use App\MealComponentOption;
use App\MealPackageAddon;
use App\MealPackageComponentOption;
use App\Store;

class Bag
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @var App\Store
     */
    protected $store;

    /**
     *
     * @param array $items
     */
    public function __construct($_items, Store $store)
    {
        $this->store = $store;

        $items = [];

        // Deduplicate items
        collect($_items)->map(function ($item) use (&$items) {
            $itemId = $this->getItemId($item);

            if (!isset($items[$itemId])) {
                $items[$itemId] = $item;
            } else {
                $items[$itemId]['quantity'] += $item['quantity'];
            }
        });

        $this->items = array_values($items);
    }

    public function getItemId($item)
    {
        return md5(
            json_encode([
                'meal' => $item['meal']['id'],
                'meal_package' => $item['meal_package'],
                'size' => $item['size'] ?? null,
                'components' => $item['components'] ?? [],
                'addons' => $item['addons'] ?? []
            ])
        );
    }

    public function getItems()
    {
        $items = [];
        $meals = $this->store
            ->meals()
            ->get()
            ->keyBy('id');

        collect($this->items)->map(function ($item) use (&$items, $meals) {
            $itemId = $this->getItemId($item);

            if (isset($item['meal_package']) && $item['meal_package']) {
                // Repeat for item quantity
                for ($i = 0; $i < $item['quantity']; $i++) {
                    // Add regular pagacke means
                    foreach ($item['meal']['meals'] as $meal) {
                        if (!isset($items[$itemId])) {
                            $items[$itemId] = [
                                'meal' => $meal,
                                'quantity' => $meal['quantity'],
                                'price' => $meals[$itemId]->price
                            ];
                        } else {
                            $items[$itemId]['quantity'] += $meal['quantity'];
                        }
                    }

                    if (isset($item['components']) && $item['components']) {
                        foreach (
                            $item['components']
                            as $componentId => $choices
                        ) {
                            foreach ($choices as $optionId) {
                                $option = MealPackageComponentOption::find(
                                    $optionId
                                );

                                $meals = MealMealPackageComponentOption::where([
                                    'meal_package_component_option_id' => $optionId
                                ])->get();

                                foreach ($meals as $meal) {
                                    $mealItem = [
                                        'meal' => $meal,
                                        'meal_package' => false,
                                        'quantity' => $meal->pivot->quantity,
                                        'price' => $meal->price,
                                        'size' => [
                                            'id' => $meal->pivot->meal_size_id
                                        ],
                                        'quantity' => $meal->pivot->quantity
                                    ];

                                    $mealItemId = $this->getItemId($mealItem);

                                    if (!isset($items[$mealItemId])) {
                                        $items[$mealItemId] = $mealItem;
                                    } else {
                                        $items[$mealItemId]['quantity'] +=
                                            $meal->pivot->quantity;
                                    }
                                }
                            }
                        }
                    }
                    if (isset($item['addons']) && $item['addons']) {
                        foreach ($item['addons'] as $addonId) {
                            $addon = MealPackageAddon::find($addonId);
                        }
                    }
                }
            } else {
                $mealId = $item['meal']['id'];
                $itemId = $this->getItemId($item);
                $price = $meals[$mealId]->price;

                // Ensure size variations are counted separately
                if (isset($item['size']) && $item['size']) {
                    $price = $item['size']['price'];
                }

                $item['price'] = $price;

                if (!isset($items[$itemId])) {
                    $items[$itemId] = $item;
                } else {
                    $items[$itemId]['quantity'] += $item['quantity'];
                }
            }
        });

        return array_values($items);
    }

    /**
     * Get bag total
     *
     * @return float
     */
    public function getTotal()
    {
        $total = 0.0;

        foreach ($this->getItems() as $item) {
            $price = $item['price'];
            if (isset($item['components']) && $item['components']) {
                foreach ($item['components'] as $componentId => $choices) {
                    foreach ($choices as $optionId) {
                        $option = MealComponentOption::find($optionId);
                        $price += $option->price;
                    }
                }
            }
            if (isset($item['addons']) && $item['addons']) {
                foreach ($item['addons'] as $addonId) {
                    $addon = MealAddon::find($addonId);
                    $price += $addon->price;
                }
            }
            $total += $price * $item['quantity'];
        }

        return $total;
    }
}
