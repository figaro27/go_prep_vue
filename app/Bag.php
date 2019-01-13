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
    public function __construct($items)
    {
        $this->items = $items;
    }

    public function getItems() {
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

        foreach($this->items as $item) {
          $total += $item['quantity'] * $item['meal']['price'];
        }

        return $total;
    }
}
