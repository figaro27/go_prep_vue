<?php

namespace App;

use App\MealComponentOption;
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
                'size' => $item['size'],
                'components' => $item['components'],
                'addons' => $item['addons']
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
            if (isset($item['meal_package']) && $item['meal_package']) {
                for ($i = 0; $i < $item['quantity']; $i++) {
                    foreach ($item['meal']['meals'] as $meal) {
                        $itemId = $meal['id'];
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
