<?php

namespace App;
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
            $itemId =
                isset($item['size']) && $item['size']
                    ? $item['meal']['id'] . '-' . $item['size']['id']
                    : $item['meal']['id'];

            if (!isset($items[$itemId])) {
                $items[$itemId] = $item;
            } else {
                $items[$itemId]['quantity'] += $item['quantity'];
            }
        });

        $this->items = array_values($items);
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
                $itemId = $item['meal']['id'];
                $price = $meals[$itemId]->price;

                // Ensure size variations are counted separately
                if (isset($item['size']) && $item['size']) {
                    $itemId .= '-' . $item['size']['id'];
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
            $total += $item['quantity'] * $item['price'];
        }

        return $total;
    }
}
