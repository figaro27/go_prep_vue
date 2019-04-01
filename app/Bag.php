<?php

namespace App;

class Bag
{
    /**
     * @var array
     */
    protected $items;

    /**
     *
     * @param array $items
     */
    public function __construct($_items)
    {
        $items = [];

        collect($_items)->map(function ($item) use (&$items) {
            if (!isset($items[$item['meal']['id']])) {
                $items[$item['meal']['id']] = $item;
            } else {
                $items[$item['meal']['id']]['quantity'] += $item['quantity'];
            }
        });

        $this->items = array_values($items);
    }

    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get bag total
     *
     * @return float
     */
    public function getTotal()
    {
        $total = 0.0;

        foreach ($this->items as $item) {
            $total += $item['quantity'] * $item['meal']['price'];
        }

        return $total;
    }
}
