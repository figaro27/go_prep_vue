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

    public function getMealPackageMappingId($item)
    {
        $meal_package_id = $meal_package_size_id = null;

        if (isset($item['meal_package']) && $item['meal_package']) {
            if (isset($item['meal']) && isset($item['meal']['id'])) {
                $meal_package_id = (int) $item['meal']['id'];
            }

            if (isset($item['size']) && isset($item['size']['id'])) {
                $meal_package_size_id = (int) $item['size']['id'];
            }
        }

        return md5(
            json_encode([
                'meal_package_id' => $meal_package_id, // contained in package
                'meal_package_size_id' => $meal_package_size_id,
                'meal_package_title' => $item['meal']['title'],
                'guid' => $item['guid']
            ])
        );
    }

    public function getItemId($item)
    {
        return md5(
            json_encode([
                'meal' => $item['meal']['id'],
                'meal_package' => $item['meal_package'] ?? false,
                'meal_package_id' => $item['meal_package_id'] ?? null, // contained in package,
                'meal_package_title' => $item['meal_package_title'] ?? null,
                'meal_package_size_id' => $item['meal_package_size_id'] ?? null,
                'price' => $item['price'] ?? null,
                'size' => $item['size'] ?? null,
                'meal_size_id' => $item['meal_size_id'] ?? null,
                'components' => $item['components'] ?? [],
                'addons' => $item['addons'] ?? [],
                'special_instructions' => $item['special_instructions'] ?? [],
                'delivery_day' =>
                    isset($item['delivery_day']) && $item['delivery_day']
                        ? $item['delivery_day']
                        : null,
                'delivery_date' =>
                    isset($item['delivery_date']) && $item['delivery_date']
                        ? $item['delivery_date']
                        : null,
                'guid' => isset($item['guid']) ? $item['guid'] : null
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

        $meal_package_mapping = [];

        collect($this->items)->map(function ($item) use (
            &$items,
            &$meal_package_mapping,
            $meals
        ) {
            $itemId = $this->getItemId($item);
            if (isset($item['meal_package']) && $item['meal_package']) {
                // Repeat for item quantity
                $mappingId = $this->getMealPackageMappingId($item);
                $customTitle = isset($item['customTitle'])
                    ? $item['customTitle']
                    : null;
                $customSize = isset($item['customSize'])
                    ? $item['customSize']
                    : null;
                if (!isset($meal_package_mapping[$mappingId])) {
                    $meal_package_mapping[$mappingId] = 0;
                }

                $categoryId = null;
                if (isset($item['meal']['category_id'])) {
                    $categoryId = $item['meal']['category_id'];
                }
                if (isset($item['category_id'])) {
                    $categoryId = $item['category_id'];
                }

                if ($this->store->modules->multipleDeliveryDays) {
                    $customTitle = $item['meal']['title'];
                }
                $meal_package_mapping[$mappingId] += $item['quantity'];

                for ($i = 0; $i < $item['quantity']; $i++) {
                    // Add regular package meals
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
                                            'price' =>
                                                $mealOption->price /
                                                $mealOption->quantity,
                                            'meal_size_id' =>
                                                $mealOption->meal_size_id,
                                            'special_instructions' => isset(
                                                $meal['special_instructions']
                                            )
                                                ? $meal['special_instructions']
                                                : null,
                                            'emailRecipient' => isset(
                                                $item['emailRecipient']
                                            )
                                                ? $item['emailRecipient']
                                                : null,
                                            'meal_package_variation' => true
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
                                                'price' =>
                                                    $mealOption->price /
                                                    $mealOption->quantity,
                                                'meal_size_id' =>
                                                    $mealOption->meal_size_id,
                                                'special_instructions' => isset(
                                                    $optionItem[
                                                        'special_instructions'
                                                    ]
                                                )
                                                    ? $optionItem[
                                                        'special_instructions'
                                                    ]
                                                    : null,
                                                'meal_package_variation' => true
                                            ]);
                                        }
                                    }
                                }

                                foreach ($meals as $meal) {
                                    $title = $item['meal']['title'];
                                    if (
                                        isset($item['size']) &&
                                        isset($item['size']['title'])
                                    ) {
                                        $title .=
                                            ' - ' . $item['size']['title'];
                                    }

                                    $mealItem = [
                                        'meal' => $meal['meal'],
                                        'meal_package' => true,
                                        'meal_package_title' => $title,
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
                                            : null,
                                        'delivery_day' =>
                                            isset($item['delivery_day']) &&
                                            $item['delivery_day']
                                                ? $item['delivery_day']
                                                : null,
                                        'emailRecipient' => isset(
                                            $item['emailRecipient']
                                        )
                                            ? $item['emailRecipient']
                                            : null,
                                        'meal_package_variation' =>
                                            $meal['meal_package_variation'],
                                        'mappingId' => $mappingId,
                                        'customTitle' => $customTitle,
                                        'customSize' => $customSize,
                                        'guid' => isset($item['guid'])
                                            ? $item['guid']
                                            : null
                                    ];

                                    $mealItemId = isset($meal['item_id'])
                                        ? $meal['item_id']
                                        : $this->getItemId($mealItem);

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
                                        // 'price' => $mealOption->price,
                                        'price' =>
                                            ($addon->price +
                                                $mealOption->price) /
                                            $mealOption->quantity,
                                        'meal_size_id' =>
                                            $mealOption->meal_size_id,
                                        'special_instructions' => isset(
                                            $mealOption['special_instructions']
                                        )
                                            ? $mealOption[
                                                'special_instructions'
                                            ]
                                            : null,
                                        'meal_package_variation' => true
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
                                            'price' =>
                                                ($addon->price +
                                                    $mealOption->price) /
                                                $mealOption->quantity,
                                            'meal_size_id' =>
                                                $mealOption->meal_size_id,
                                            'special_instructions' => isset(
                                                $addonItem[
                                                    'special_instructions'
                                                ]
                                            )
                                                ? $addonItem[
                                                    'special_instructions'
                                                ]
                                                : null,
                                            'meal_package_variation' => true
                                        ]);
                                    }
                                }
                            }

                            foreach ($meals as $meal) {
                                $title = $item['meal']['title'];
                                if (
                                    isset($item['size']) &&
                                    isset($item['size']['title'])
                                ) {
                                    $title .= ' - ' . $item['size']['title'];
                                }

                                $customTitle = isset($item['customTitle'])
                                    ? $item['customTitle']
                                    : null;
                                $customSize = isset($item['customSize'])
                                    ? $item['customSize']
                                    : null;
                                $mealItem = [
                                    'meal' => $meal['meal'],
                                    'meal_package' => true,
                                    'meal_package_title' => $title,
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
                                    'quantity' => $meal['quantity'],
                                    'special_instructions' => isset(
                                        $meal['special_instructions']
                                    )
                                        ? $meal['special_instructions']
                                        : null,
                                    'delivery_day' =>
                                        isset($item['delivery_day']) &&
                                        $item['delivery_day']
                                            ? $item['delivery_day']
                                            : null,
                                    'meal_package_variation' =>
                                        $meal['meal_package_variation'],
                                    'mappingId' => $mappingId,
                                    'customTitle' => $customTitle,
                                    'customSize' => $customSize,
                                    'guid' => isset($item['guid'])
                                        ? $item['guid']
                                        : null
                                ];

                                $mealItemId = isset($meal['item_id'])
                                    ? $meal['item_id']
                                    : $this->getItemId($mealItem);

                                if (!isset($items[$mealItemId])) {
                                    $items[$mealItemId] = $mealItem;
                                } else {
                                    $items[$mealItemId]['quantity'] +=
                                        $meal['quantity'];
                                }
                            }
                        }
                    }
                    // Top level package meals not in a package size
                    if (
                        $item['size'] === null &&
                        (is_array($item['meal']['meals']) ||
                            is_object($item['meal']['meals']))
                    ) {
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
                                'special_instructions' => isset(
                                    $meal['special_instructions']
                                )
                                    ? $meal['special_instructions']
                                    : null,
                                'delivery_day' =>
                                    isset($item['delivery_day']) &&
                                    $item['delivery_day']
                                        ? $item['delivery_day']
                                        : null,
                                'emailRecipient' => isset(
                                    $item['emailRecipient']
                                )
                                    ? $item['emailRecipient']
                                    : null,
                                'meal_package_variation' => false,
                                'mappingId' => $mappingId,
                                'customTitle' => $customTitle,
                                'customSize' => $customSize,
                                'meal_package_variation' => isset(
                                    $meal['meal_package_variation']
                                )
                                    ? $meal['meal_package_variation']
                                    : 0,
                                'guid' => isset($item['guid'])
                                    ? $item['guid']
                                    : null,
                                'top_level' => true,
                                'category_id' => $categoryId
                            ];

                            $mealItemId = isset($meal['item_id'])
                                ? $meal['item_id']
                                : $this->getItemId($mealItem);

                            if (!isset($items[$mealItemId])) {
                                $items[$mealItemId] = $mealItem;
                            } else {
                                $items[$mealItemId]['quantity'] +=
                                    $mealItem['quantity'];
                            }
                        }
                        // Top level package meals in a package size
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
                                    'price' => isset($meal['price'])
                                        ? $meal['price']
                                        : 0,
                                    'size' => [
                                        'id' => $meal['meal_size_id']
                                            ? $meal['meal_size_id']
                                            : null
                                    ],
                                    'quantity' => $meal['quantity'],
                                    'special_instructions' => isset(
                                        $meal['special_instructions']
                                    )
                                        ? $meal['special_instructions']
                                        : null,
                                    'delivery_day' =>
                                        isset($item['delivery_day']) &&
                                        $item['delivery_day']
                                            ? $item['delivery_day']
                                            : null,
                                    'emailRecipient' => isset(
                                        $item['emailRecipient']
                                    )
                                        ? $item['emailRecipient']
                                        : null,
                                    'meal_package_variation' => isset(
                                        $meal['meal_package_variation']
                                    )
                                        ? $meal['meal_package_variation']
                                        : 0,
                                    'mappingId' => $mappingId,
                                    'customTitle' => $customTitle,
                                    'customSize' => $customSize,
                                    'guid' => isset($item['guid'])
                                        ? $item['guid']
                                        : null,
                                    'top_level' => true,
                                    'category_id' => $categoryId
                                ];

                                $mealItemId = isset($meal['item_id'])
                                    ? $meal['item_id']
                                    : $this->getItemId($mealItem);

                                if (!isset($items[$mealItemId])) {
                                    $items[$mealItemId] = $mealItem;
                                } else {
                                    $items[$mealItemId]['quantity'] +=
                                        $mealItem['quantity'];
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

        $items = array_values($items);

        if ($meal_package_mapping && $items) {
            foreach ($items as &$item) {
                if (
                    isset($item['mappingId']) &&
                    isset($meal_package_mapping[$item['mappingId']])
                ) {
                    $item['package_quantity'] =
                        $meal_package_mapping[$item['mappingId']];
                }
            }
        }

        return $items;
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
                        $price += $option ? $option->price : 0;
                    }
                }
            }
            if (isset($item['addons']) && $item['addons']) {
                foreach ($item['addons'] as $addonId) {
                    $addon = MealAddon::find($addonId);
                    $price += $addon ? $addon->price : 0;
                }
            }
            $total += $price * $item['quantity'];
        }

        return $total;
    }

    public function getTotalSync()
    {
        $total = 0.0;

        foreach ($this->getItems() as $item) {
            $price = $item['price'];

            // Removing below. The added prices for any associated variations is already factored into the saved item price.

            // if (isset($item['components']) && $item['components']) {
            //     foreach ($item['components'] as $componentId => $option) {
            //         $option = MealComponentOption::find(
            //             $option['meal_component_option_id']
            //         );
            //         $price += $option ? $option->price : 0;
            //     }
            // }
            // if (isset($item['addons']) && $item['addons']) {
            //     foreach ($item['addons'] as $addon) {
            //         $addon = MealAddon::find($addon['meal_addon_id']);
            //         $price += $addon ? $addon->price : 0;
            //     }
            // }
            $total += $price;
        }

        return $total;
    }
}
