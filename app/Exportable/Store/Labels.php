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
        $production = collect();
        $dates = $this->getDeliveryDates();
        $store = $this->store;
        $params = $this->params;
        $params->date_format = $this->store->settings->date_format;
        $allDates = [];

        if ($this->orderId) {
            $orders = $this->store
                ->orders()
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

                $number = 0;
                foreach ($mealOrders as $mealOrder) {
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
                ->where('store_id', $this->store->id)
                ->whereHas('order', function ($order) {
                    $order->where('paid', 1)->where('voided', 0);
                });

            if ($this->orderId) {
                $mealOrders = $mealOrders->where('order_id', $this->orderId);
            }

            $mealOrders = $mealOrders->with('meal', 'meal.ingredients')->get();

            $totalCount = 0;
            foreach ($mealOrders as $mealOrder) {
                $totalCount += $mealOrder->quantity;
            }

            $number = 0;

            foreach ($mealOrders as $mealOrder) {
                for ($i = 1; $i <= $mealOrder->quantity; $i++) {
                    $mealOrderCopy = $mealOrder->replicate();
                    $mealOrderCopy->index = $number + $i;
                    $mealOrderCopy->totalCount = $totalCount;
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
            $reportRecord->labels += 1;
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

        $filename = 'public/' . md5(time()) . '.pdf';

        $width = $this->store->reportSettings->lab_width;
        $height = $this->store->reportSettings->lab_height;

        try {
            $logo = \App\Utils\Images::encodeB64(
                $this->store->details->logo['url']
            );
        } catch (\Exception $e) {
            $logo = $this->store->details->logo['url'];
        }

        // Temporary solution
        $testStore = Store::where('id', 13)->first();
        $whiteSpace = $testStore->details->logo['url'];

        $vars = [
            'mealOrders' => $mealOrders,
            'params' => $this->params,
            'delivery_dates' => $this->getDeliveryDates(),
            'body_classes' => implode(' ', [$this->orientation]),
            'logo' => $logo,
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
        $reportRecord->labels += 1;
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

        $mainIngredients = $meal->ingredients->toArray();

        $relations = $item->relationsToArray();

        foreach ($relations['components'] as $component) {
            $ingredients = MealComponentOption::where(
                'id',
                $component['meal_component_option_id']
            )
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
                ->with('ingredients')
                ->first()->ingredients;
            foreach ($ingredients as $ingredient) {
                if (!$ingredient->attributes['hidden']) {
                    array_push($mainIngredients, $ingredient);
                }
            }
        }

        $allIngredients = json_encode(
            array_merge($meal->attributesToArray(), [
                'ingredients' => $mainIngredients
            ])
        );

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
