<?php

namespace App;

use App\MealAddon;
use App\MealComponentOption;
use App\MealPackageAddon;
use App\MealPackageComponentOption;
use App\Store;
use stdClass;

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
                'meal_package' => $item['meal_package'] ?? false,
                'meal_package_id' => $item['meal_package_id'] ?? null, // contained in package
                'meal_package_size_id' => $item['meal_package_size_id'] ?? null,
                'size' => $item['size'] ?? null,
                'components' => $item['components'] ?? [],
                'addons' => $item['addons'] ?? [],
                'special_instructions' => $item['special_instructions'] ?? []
            ])
        );
    }

    public function getRawItems()
    {
        return $this->items;
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
                    // Add regular package meals

                    if ($item['size'] === null) {
                        foreach ($item['meal']['meals'] as $meal) {
                            // if (!$meals[$meal['id']]->active) {
                            //     //continue;
                            // }

                            $mealItem = [
                                'meal_package_title' => $item['meal']['title'],
                                'meal_package_id' => $item['meal']['id'],
                                'meal_package_size_id' => $item['size']['id'],
                                'meal' => [
                                    'id' => $meal['id']
                                ],
                                'meal_package' => true,
                                'package_price' => $item['price'],
                                'package_quantity' => $item['quantity'],
                                'quantity' => $meal['quantity'],
                                'price' => $meal['price'],
                                'size' => [
                                    'id' => $meal['meal_size_id'] ?? null
                                    // ? $meal['meal_size']['id']
                                    // : null
                                ],
                                'quantity' => $meal['quantity'],
                                'special_instructions' =>
                                    $meal['special_instructions'] ?? null
                            ];

                            $mealItemId = $this->getItemId($mealItem);

                            if (!isset($items[$mealItemId])) {
                                $items[$mealItemId] = $mealItem;
                            } else {
                                $items[$mealItemId]['quantity'] +=
                                    $mealItem['quantity'];
                            }
                        }
                    } else {
                        if (
                            isset($item) &&
                            isset($item['size']) &&
                            isset($item['size']['meals'])
                        ) {
                            foreach ($item['size']['meals'] as $meal) {
                                // if (!$meals[$meal['id']]->active) {
                                //     //continue;
                                // }

                                $mealItem = [
                                    'meal_package_title' =>
                                        $item['meal']['title'] .
                                        ' - ' .
                                        $item['size']['title'],
                                    'meal_package_id' => $item['meal']['id'],
                                    'meal_package_size_id' =>
                                        $item['size']['id'],
                                    'meal' => [
                                        'id' => $meal['id']
                                    ],
                                    'meal_package' => true,
                                    'package_price' => $item['price'],
                                    'package_quantity' => $item['quantity'],
                                    'quantity' => $meal['quantity'],
                                    'price' => 0,
                                    'size' => [
                                        'id' => $meal['meal_size_id']
                                            ? $meal['meal_size_id']
                                            : null
                                    ],
                                    'quantity' => $meal['quantity'],
                                    'special_instructions' =>
                                        $meal['special_instructions'] ?? null
                                ];

                                $mealItemId = $this->getItemId($mealItem);

                                if (!isset($items[$mealItemId])) {
                                    $items[$mealItemId] = $mealItem;
                                } else {
                                    $items[$mealItemId]['quantity'] +=
                                        $mealItem['quantity'];
                                }
                            }
                        }
                    }

                    if (isset($item['components']) && $item['components']) {
                        foreach (
                            $item['components']
                            as $componentId => $choices
                        ) {
                            foreach ($choices as $optionId => $optionItems) {
                                $optionItems = collect($optionItems);

                                $option = MealPackageComponentOption::find(
                                    $optionId
                                );

                                $meals = collect();

                                if (!$option->selectable) {
                                    $mealOptions = MealMealPackageComponentOption::where(
                                        [
                                            'meal_package_component_option_id' => $optionId
                                        ]
                                    )->get();

                                    foreach ($mealOptions as $mealOption) {
                                        $meals->push([
                                            'meal' => [
                                                'id' => $mealOption->meal_id
                                            ],
                                            'quantity' => $mealOption->quantity,
                                            'price' => $mealOption->price,
                                            'meal_size_id' =>
                                                $mealOption->meal_size_id
                                        ]);
                                    }
                                } else {
                                    foreach ($optionItems as $optionItem) {
                                        $mealOption = MealMealPackageComponentOption::where(
                                            [
                                                'meal_package_component_option_id' => $optionId
                                            ]
                                        )
                                            ->where(
                                                'meal_id',
                                                $optionItem['meal_id']
                                            )
                                            ->where(
                                                'meal_size_id',
                                                $optionItem['meal_size_id'] ??
                                                    null
                                            )
                                            ->first();

                                        if ($mealOption) {
                                            $meals->push([
                                                'meal' => [
                                                    'id' => $mealOption->meal_id
                                                ],
                                                'quantity' =>
                                                    $optionItem['quantity'],
                                                'price' => $mealOption->price,
                                                'meal_size_id' =>
                                                    $mealOption->meal_size_id,
                                                'special_instructions' => isset(
                                                    $optionItem[
                                                        'specialInstructions'
                                                    ]
                                                )
                                                    ? $optionItem[
                                                        'specialInstructions'
                                                    ]
                                                    : null
                                            ]);
                                        }
                                    }
                                }

                                foreach ($meals as $meal) {
                                    $mealItem = [
                                        'meal' => $meal['meal'],
                                        'meal_package' => true,
                                        'meal_package_title' =>
                                            $item['meal']['title'] .
                                            ' - ' .
                                            $item['size']['title'],
                                        'meal_package_id' =>
                                            $item['meal']['id'],
                                        'meal_package_size_id' =>
                                            $item['size']['id'],
                                        'package_price' => $item['price'],
                                        'package_quantity' => $item['quantity'],
                                        'quantity' => $meal['quantity'],
                                        'price' => $meal['price'],
                                        'size' => [
                                            'id' => $meal['meal_size_id']
                                        ],
                                        'quantity' => $meal['quantity'],
                                        'special_instructions' => isset(
                                            $meal['special_instructions']
                                        )
                                            ? $meal['special_instructions']
                                            : null
                                    ];

                                    $mealItemId = $this->getItemId($mealItem);

                                    if (!isset($items[$mealItemId])) {
                                        $items[$mealItemId] = $mealItem;
                                    } else {
                                        $items[$mealItemId]['quantity'] +=
                                            $meal['quantity'];
                                    }
                                }
                            }
                        }
                    }
                    if (isset($item['addons']) && $item['addons']) {
                        foreach ($item['addons'] as $addonId => $addonItems) {
                            $addonItems = collect($addonItems);

                            $addon = MealPackageAddon::find($addonId);

                            $meals = collect();

                            if (!$addon->selectable) {
                                $mealOptions = MealMealPackageAddon::where([
                                    'meal_package_addon_id' => $addonId
                                ])->get();

                                foreach ($mealOptions as $mealOption) {
                                    $meals->push([
                                        'meal' => [
                                            'id' => $mealOption->meal_id
                                        ],
                                        'quantity' => $mealOption->quantity,
                                        'price' => $mealOption->price,
                                        'meal_size_id' =>
                                            $mealOption->meal_size_id
                                    ]);
                                }
                            } else {
                                foreach ($addonItems as $addonItem) {
                                    $mealOption = MealMealPackageAddon::where([
                                        'meal_package_addon_id' => $addonId
                                    ])
                                        ->where(
                                            'meal_id',
                                            $addonItem['meal_id']
                                        )
                                        ->where(
                                            'meal_size_id',
                                            $addonItem['meal_size_id'] ?? null
                                        )
                                        ->first();

                                    if ($mealOption) {
                                        $meals->push([
                                            'meal' => [
                                                'id' => $mealOption->meal_id
                                            ],
                                            'quantity' =>
                                                $addonItem['quantity'],
                                            'price' => $mealOption->price,
                                            'meal_size_id' =>
                                                $mealOption->meal_size_id
                                        ]);
                                    }
                                }
                            }

                            foreach ($meals as $meal) {
                                $mealItem = [
                                    'meal' => $meal['meal'],
                                    'meal_package' => true,
                                    'meal_package_title' =>
                                        $item['meal']['title'] .
                                        ' - ' .
                                        $item['size']['title'],
                                    'meal_package_id' => $item['meal']['id'],
                                    'meal_package_size_id' =>
                                        $item['size']['id'],
                                    'package_price' => $item['price'],
                                    'package_quantity' => $item['quantity'],
                                    'quantity' => $meal['quantity'],
                                    'price' => $meal['price'],
                                    'size' => [
                                        'id' => $meal['meal_size_id']
                                    ],
                                    'quantity' => $meal['quantity']
                                ];

                                $mealItemId = $this->getItemId($mealItem);

                                if (!isset($items[$mealItemId])) {
                                    $items[$mealItemId] = $mealItem;
                                } else {
                                    $items[$mealItemId]['quantity'] +=
                                        $meal['quantity'];
                                }
                            }
                        }
                    }
                }
            } else {
                $mealId = $item['meal']['id'];
                $itemId = $this->getItemId($item);
                // $price = $meals[$mealId]->price;

                // // Ensure size variations are counted separately
                // if (isset($item['size']) && $item['size']) {
                //     $price = $item['size']['price'];
                // }

                // $item['price'] = $price;

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
