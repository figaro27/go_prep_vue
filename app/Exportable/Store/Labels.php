<?php

namespace App\Exportable\Store;

use App\Meal;
use App\Store;
use App\MealSize;
use App\ProductionGroup;
use App\LineItem;
use App\Exportable\Exportable;
use Illuminate\Support\Carbon;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use App\MealOrder;
use App\MealAddon;
use App\MealComponentOption;
use App\Utils\Data\Format;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;
use App\Ingredient;
use App\ReportRecord;
use App\Order;

class Labels
{
    use Exportable;

    protected $store;
    protected $allDates;
    protected $orderId;

    public function __construct(Store $store, $params = [])
    {
        $this->store = $store;
        $this->params = collect($params);
        $this->orientation = 'portrait';
        $this->page = $params->get('page', 1);
        $this->perPage = 10000;
        $this->orderId = $params->get('order_id');
    }

    public function filterVars($vars)
    {
        $vars['dates'] = $this->allDates;
        $vars['body_classes'] = implode(' ', [$this->orientation, 'label']);
        return $vars;
    }

    public function exportData($type = null)
    {
        $this->store->orders = Order::whereIn(
            'store_id',
            $this->store->active_child_store_ids
        )->orderBy('created_at', 'desc');

        $this->params->put('store', $this->store->details->name);
        $this->params->put('report', 'Labels');
        $this->params->put('date', Carbon::now()->format('m-d-Y'));

        $production = collect();
        $dates = $this->getDeliveryDates();
        $store = $this->store;
        $params = $this->params;
        $params->date_format = $this->store->settings->date_format;
        $allDates = [];

        if ($this->orderId) {
            $orders = $this->store->orders
                ->where([
                    'paid' => 1,
                    // 'voided' => 0,
                    'id' => $this->orderId
                ])
                ->get();
        } else {
            $orders = $this->store->getOrders(null, $dates, true);
            $orders = $orders->where('voided', 0);
        }

        if (!$this->store->modules->multipleDeliveryDays) {
            $total = $orders->count();
            $orders = $orders
                ->slice(($this->page - 1) * $this->perPage)
                ->take($this->perPage);
            $numDone = $this->page * $this->perPage;

            if ($numDone < $total) {
                $this->page++;
            } else {
                $this->page = null;
            }

            $orders->map(function ($order) use (
                &$allDates,
                &$production,
                $dates
            ) {
                $date = "";
                if ($order->delivery_date) {
                    $date = $order->delivery_date->toDateString();
                }

                $mealOrders = $order
                    ->meal_orders()
                    ->with('meal', 'meal.ingredients');
                $lineItemsOrders = $order->lineItemsOrders()->with('lineItem');

                $mealOrders = $mealOrders->get();
                $lineItemsOrders = $lineItemsOrders->get();

                $totalCount = 0;
                foreach ($mealOrders as $mealOrder) {
                    $totalCount += $mealOrder->quantity;
                }

                $storeIds = [];
                foreach ($mealOrders as $mealOrder) {
                    if (!in_array($mealOrder->store_id, $storeIds)) {
                        $storeIds[] = $mealOrder->store_id;
                    }
                }
                $storeLogos = [];

                foreach ($storeIds as $storeId) {
                    $store = Store::where('id', $storeId)->first();
                    try {
                        $logo = \App\Utils\Images::encodeB64(
                            $store->details->logo['url']
                        );
                    } catch (\Exception $e) {
                        $logo = $store->details->logo['url'];
                    }
                    $storeLogos[$storeId] = $logo;
                }

                $number = 0;
                foreach ($mealOrders as $mealOrder) {
                    $mealOrder->logo = $storeLogos[$mealOrder->store_id];
                    for ($i = 1; $i <= $mealOrder->quantity; $i++) {
                        $mealOrderCopy = $mealOrder->replicate();
                        $mealOrderCopy->index = $number + $i;
                        $mealOrderCopy->totalCount = $totalCount;
                        $production->push($mealOrderCopy);
                    }
                    $number += $mealOrder->quantity;
                }

                // Not sure if it makes sense to generate labels for line items especially for service based items

                // $totalCount = 0;
                // foreach ($lineItemsOrders as $lineItemOrder){
                //     $totalCount += $lineItemOrder->quantity;
                // }

                // $number = 0;
                // foreach ($lineItemsOrders as $i => $lineItemOrder) {
                //     $lineItemOrderCopy = $lineItemOrder->replicate();
                //     $lineItemOrderCopy->index = $number + $i;
                //     $lineItemOrderCopy->totalCount = $totalCount;
                //     for ($i = 1; $i <= $lineItemOrder->quantity; $i++) {
                //         $production->push($lineItemOrderCopy);
                //     }
                // }
            });

            $output = $production->map(function ($item) {
                $json = $this->getAllIngredients($item);
                $item->json = $json;
                return $item;
            });

            return $output;
        } else {
            $mealOrders = MealOrder::where(
                'delivery_date',
                '>=',
                $dates['from']
            )
                ->where('delivery_date', '<=', $dates['to'])
                ->whereIn('store_id', $this->store->active_child_store_ids)
                ->whereHas('order', function ($order) {
                    $order->where('paid', 1)->where('voided', 0);
                });

            if ($this->orderId) {
                $mealOrders = $mealOrders->where('order_id', $this->orderId);
            }

            $mealOrders = $mealOrders->with('meal', 'meal.ingredients')->get();

            $storeIds = [];
            foreach ($mealOrders as $mealOrder) {
                if (!in_array($mealOrder->store_id, $storeIds)) {
                    $storeIds[] = $mealOrder->store_id;
                }
            }
            $storeLogos = [];

            foreach ($storeIds as $storeId) {
                $store = Store::where('id', $storeId)->first();
                try {
                    $logo = \App\Utils\Images::encodeB64(
                        $store->details->logo['url']
                    );
                } catch (\Exception $e) {
                    $logo = $store->details->logo['url'];
                }
                $storeLogos[$storeId] = $logo;
            }

            $totalCount[] = 0;

            foreach ($mealOrders as $mealOrder) {
                $mealOrder->logo = $storeLogos[$mealOrder->store_id];
                $order_id = $mealOrder->order_id;

                if (!isset($totalCount[$order_id])) {
                    $totalCount[$order_id] = 0;
                }
                $totalCount[$order_id] += $mealOrder->quantity;
            }

            $index[] = 0;

            foreach ($mealOrders as $mealOrder) {
                for ($i = 1; $i <= $mealOrder->quantity; $i++) {
                    $order_id = $mealOrder->order_id;
                    if (!isset($index[$order_id])) {
                        $index[$order_id] = 0;
                    }
                    $index[$order_id] += 1;
                    $mealOrder->index = $index[$order_id];

                    $mealOrderCopy = $mealOrder->replicate();
                    $mealOrderCopy->totalCount =
                        $totalCount[$mealOrderCopy->order_id];
                    $production->push($mealOrderCopy);
                }
            }

            $output = $production->map(function ($item) {
                $json = $this->getAllIngredients($item);
                $item->json = $json;
                return $item;
            });

            $reportRecord = ReportRecord::where(
                'store_id',
                $this->store->id
            )->first();
            $reportRecord->meal_labels += 1;
            $reportRecord->update();

            return $output;
        }
    }

    public function export($type)
    {
        if (!in_array($type, ['pdf', 'b64'])) {
            return null;
        }

        Log::info('Starting label print');

        $mealOrders = $this->exportData();

        // Ordering labels by meal instead of order. Possibly make this a user option.
        $mealOrders = $mealOrders->groupBy('meal_id')->flatten();

        Log::info('Found ' . count($mealOrders) . ' orders');

        if (!count($mealOrders)) {
            throw new \Exception('No meal orders');
        }

        $filename =
            'public/' .
            $this->params['store'] .
            '_labels_' .
            $this->params['date'] .
            '.pdf';

        $width = $this->store->reportSettings->lab_width;
        $height = $this->store->reportSettings->lab_height;

        // Temporary solution
        $testStore = Store::where('id', 13)->first();
        $whiteSpace = $testStore->details->logo['url'];

        $vars = [
            'mealOrders' => $mealOrders,
            'params' => $this->params,
            'delivery_dates' => $this->getDeliveryDates(),
            'body_classes' => implode(' ', [$this->orientation]),
            'whiteSpace' => $whiteSpace
        ];

        $html = view($this->exportPdfView(), $vars)->render();
        Log::info('Page HTML: ' . $html);

        $page = Browsershot::html($html)
            ->paperSize($width, $height, 'in')
            ->waitUntilNetworkIdle()
            ->waitForFunction('window.status === "ready"', 100, 3000);

        $output = $page->pdf();

        Log::info('Saved to ' . $filename);

        $reportRecord = ReportRecord::where(
            'store_id',
            $this->store->id
        )->first();
        $reportRecord->meal_labels += 1;
        $reportRecord->update();

        if ($type === 'pdf') {
            Storage::disk('local')->put($filename, $output);
            return Storage::url($filename);
        } elseif ($type === 'b64') {
            return base64_encode($output);
        }
    }

    public function getAllIngredients($item)
    {
        $meal = $item->meal;

        if ($item->meal_size_id) {
            $meal = MealSize::where('id', $item->meal_size_id)->first();
        }

        $mainIngredients =
            $meal && $meal->ingredients ? $meal->ingredients->toArray() : [];

        $relations = $item->relationsToArray();

        foreach ($relations['components'] as $component) {
            $ingredients = MealComponentOption::where(
                'id',
                $component['meal_component_option_id']
            )
                ->withTrashed()
                ->with('ingredients')
                ->first()->ingredients;
            foreach ($ingredients as $ingredient) {
                if (!$ingredient->attributes['hidden']) {
                    array_push($mainIngredients, $ingredient);
                }
            }
        }

        foreach ($relations['addons'] as $addon) {
            $ingredients = MealAddon::where('id', $addon['meal_addon_id'])
                ->withTrashed()
                ->with('ingredients')
                ->first()->ingredients;
            foreach ($ingredients as $ingredient) {
                if (!$ingredient->attributes['hidden']) {
                    array_push($mainIngredients, $ingredient);
                }
            }
        }

        $allIngredients = $meal
            ? json_encode(
                array_merge($meal->attributesToArray(), [
                    'ingredients' => $mainIngredients
                ])
            )
            : '';

        // Remove ingredients added on or duplication occurs
        // foreach ($relations['components'] as $component) {
        //     $ingredients = MealComponentOption::where(
        //         'id',
        //         $component['meal_component_option_id']
        //     )
        //         ->with('ingredients')
        //         ->first()->ingredients;
        //     foreach ($ingredients as $ingredient) {
        //         if (!$ingredient->attributes['hidden']) {
        //             $meal->ingredients->pop($ingredient);
        //         }
        //     }
        // }

        // foreach ($relations['addons'] as $addon) {
        //     $ingredients = MealAddon::where('id', $addon['meal_addon_id'])
        //         ->with('ingredients')
        //         ->first()->ingredients;
        //     foreach ($ingredients as $ingredient) {
        //         if (!$ingredient->attributes['hidden']) {
        //             $meal->ingredients->pop($ingredient);
        //         }
        //     }
        // }

        return $allIngredients;
    }

    public function exportPdfView()
    {
        return 'reports.label_pdf';
    }
}
