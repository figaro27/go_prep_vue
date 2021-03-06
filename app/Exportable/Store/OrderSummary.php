<?php
namespace App\Exportable\Store;
use App\Exportable\Exportable;
use App\Store;
use App\StoreModule;
use App\User;
use App\MealOrder;
use Illuminate\Support\Carbon;
use App\ReportRecord;

class OrderSummary
{
    use Exportable;
    protected $store;
    public function __construct(Store $store, $params)
    {
        $this->store = $store;
        $this->params = $params;
        $this->orientation = 'portrait';
        $this->page = $params->get('page', 1);
        $this->perPage = 50;
    }
    public function exportData($type = null)
    {
        $this->params->put('store', $this->store->details->name);
        $this->params->put('report', 'Order Summaries');
        $this->params->put('date', Carbon::now()->format('m-d-Y'));

        $dateRange = $this->getDeliveryDates();
        $params = $this->params;
        $params['dailyOrderNumbers'] = $this->store->modules->dailyOrderNumbers;
        $params->date_format = $this->store->settings->date_format;
        // if ($params->has('fulfilled')) {
        //     $fulfilled = $params->get('fulfilled');
        // } else {
        //     $fulfilled = 0;
        // }
        $orders = $this->store->orders()->where(['paid' => 1, 'voided' => 0]);
        // ->where(['fulfilled' => $fulfilled, 'paid' => 1]);
        $orders = $orders->where(function ($query) use ($dateRange) {
            $query
                ->where(function ($query1) use ($dateRange) {
                    $query1->where('isMultipleDelivery', 0);

                    if (isset($dateRange['from'])) {
                        $from = Carbon::parse($dateRange['from']);
                        $query1->where(
                            'delivery_date',
                            '>=',
                            $from->format('Y-m-d')
                        );
                    }

                    if (isset($dateRange['to'])) {
                        $to = Carbon::parse($dateRange['to']);
                        $query1->where(
                            'delivery_date',
                            '<=',
                            $to->format('Y-m-d')
                        );
                    }
                })
                ->orWhere(function ($query2) use ($dateRange) {
                    $query2
                        ->where('isMultipleDelivery', 1)
                        ->whereHas('meal_orders', function ($subquery1) use (
                            $dateRange
                        ) {
                            $subquery1->whereNotNull(
                                'meal_orders.delivery_date'
                            );

                            if (isset($dateRange['from'])) {
                                $from = Carbon::parse($dateRange['from']);
                                $subquery1->where(
                                    'meal_orders.delivery_date',
                                    '>=',
                                    $from->format('Y-m-d')
                                );
                            }

                            if (isset($dateRange['to'])) {
                                $to = Carbon::parse($dateRange['to']);
                                $subquery1->where(
                                    'meal_orders.delivery_date',
                                    '<=',
                                    $to->format('Y-m-d')
                                );
                            }
                        });
                });
        });

        // Removing orders from reports that just contain gift cards
        $orders = $orders->where(function ($order) {
            $order
                ->whereHas('meal_orders')
                ->orWhereHas('meal_package_orders')
                ->orWhereHas('lineItemsOrders');
        });

        if (isset($params['pickupLocationId'])) {
            $orders = $orders->where(
                'pickup_location_id',
                $params['pickupLocationId']
            );
        }

        if ($type === 'csv' || $type === 'xls') {
            $mealOrders = MealOrder::where('store_id', $this->store->id)
                ->get()
                ->filter(function ($mealOrder) {
                    if (
                        $mealOrder->order->paid === 0 ||
                        $mealOrder->order->voided === 1
                    ) {
                        return;
                    }
                    $dateRange = $this->getDeliveryDates();
                    if (isset($dateRange['from'])) {
                        $from = Carbon::parse($dateRange['from']);
                    }
                    if (isset($dateRange['to'])) {
                        $to = Carbon::parse($dateRange['to']);
                    }

                    return !$mealOrder->order->isMultipleDelivery
                        ? $mealOrder->order->delivery_date >=
                                $from->format('Y-m-d') &&
                                $mealOrder->order->delivery_date <=
                                    $to->addDays(1)->format('Y-m-d')
                        : $mealOrder->delivery_date >= $from->format('Y-m-d') &&
                                $mealOrder->delivery_date <=
                                    $to->addDays(1)->format('Y-m-d');
                });
            $customerMealOrders = $mealOrders->map(function ($mealOrder) {
                return [
                    'order_ID' => $mealOrder->order->order_number,
                    'order_placed' => $mealOrder->order->created_at->format(
                        'Y-m-d'
                    ),
                    'delivery_date' => !$mealOrder->order->isMultipleDelivery
                        ? $mealOrder->order->delivery_date->format('Y-m-d')
                        : $mealOrder->delivery_date->format('Y-m-d'),
                    'customer' => $mealOrder->order->customer_name,
                    'email' => $mealOrder->order->user->email,
                    'phone' => $mealOrder->order->customer_phone,
                    'address' => $mealOrder->order->customer_address,
                    'city' => $mealOrder->order->customer_city,
                    'state' => $mealOrder->order->customer_state,
                    'zip' => $mealOrder->order->customer_zip,
                    'delivery_instructions' =>
                        $mealOrder->order->customer_delivery,
                    'meal_id' => $mealOrder->meal->title,
                    'meal_size' => $mealOrder->meal_size
                        ? $mealOrder->meal_size->title
                        : null,
                    'meal_components' => $mealOrder->components
                        ? $mealOrder->componentsFormat
                        : null,
                    'meal_addons' => $mealOrder->addons
                        ? $mealOrder->addonsFormat
                        : null,
                    'special_instructions' => $mealOrder->special_instructions,
                    'quantity' => $mealOrder->quantity,
                    'price' =>
                        '$' .
                        number_format(
                            $mealOrder->quantity * $mealOrder->meal->price,
                            2
                        )
                ];
            });
            $customerMealOrders->prepend([
                'Order ID',
                'Order Placed',
                'Delivery Date',
                'Customer',
                'Email',
                'Phone',
                'Address',
                'City',
                'State',
                'Zip',
                'Delivery Instructions',
                'Item',
                'Size',
                'Components',
                'Addons',
                'Special Instructions',
                'Quantity',
                'Price'
            ]);
            return $customerMealOrders;
        } elseif ($type = 'pdf') {
            $customerOrders = $orders->with(['meal_orders', 'lineItemsOrders']);

            $total = $customerOrders->count();
            $customerOrders = $customerOrders
                ->get()
                ->slice(($this->page - 1) * $this->perPage)
                ->take($this->perPage);
            $numDone = $this->page * $this->perPage;

            if ($numDone < $total) {
                $this->page++;
            } else {
                $this->page = null;
            }

            $customerOrders = $customerOrders
                ->groupBy('user_id')
                ->map(function ($orders, $userId) {
                    return [
                        'user' => User::find($userId),
                        'orders' => $orders->map(function ($order) {
                            return [
                                'id' => $order->id,
                                'order_number' => $order->order_number,
                                'customer' => $order->customer_name,
                                'phone' => $order->customer_phone,
                                'address' => $order->customer_address,
                                'city' => $order->customer_city,
                                'state' => $order->customer_state,
                                'zip' => $order->customer_zip,
                                'delivery' => $order->customer_delivery,
                                'delivery_date' => $order->delivery_date,
                                'transferTime' => $order->transferTime,
                                'pickup' => $order->pickup,
                                'pickup_location_id' =>
                                    $order->pickup_location_id,
                                'pickup_location' => $order->pickup_location,
                                'dailyOrderNumber' => $order->dailyOrderNumber,
                                'notes' => $order->notes,
                                'isMultipleDelivery' =>
                                    $order->isMultipleDelivery,
                                'multipleDates' => $order->multipleDates,
                                'meal_quantities' => array_merge(
                                    [['Quantity', 'Size', 'Item']], // Heading
                                    $order
                                        ->meal_orders()
                                        ->get()
                                        ->sortBy('delivery_date')
                                        ->map(function ($mealOrder) {
                                            return [
                                                'quantity' =>
                                                    $mealOrder->quantity ?? 1,
                                                'size' => $mealOrder->base_size,
                                                'title' =>
                                                    $mealOrder->base_title
                                            ];
                                        })
                                        ->toArray()
                                ),
                                'lineItemsOrders' => array_merge(
                                    [['Extras', 'Quantity']], // Heading
                                    $order
                                        ->lineItemsOrders()
                                        ->get()
                                        ->map(function ($lineItemOrder) {
                                            return [
                                                'title' =>
                                                    $lineItemOrder->full_title,
                                                'quantity' =>
                                                    $lineItemOrder->quantity
                                            ];
                                        })
                                        ->toArray()
                                )
                            ];
                        })
                    ];
                });

            $reportRecord = ReportRecord::where(
                'store_id',
                $this->store->id
            )->first();
            $reportRecord->order_summaries += 1;
            $reportRecord->update();

            return $customerOrders->values();
        }
    }
    public function exportPdfView()
    {
        return 'reports.order_summary_pdf';
    }
}
