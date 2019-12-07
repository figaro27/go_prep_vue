<?php
namespace App\Exportable\Store;
use App\Exportable\Exportable;
use App\Store;
use App\StoreModule;
use App\User;
use App\MealOrder;
use Illuminate\Support\Carbon;
class OrdersByCustomer
{
    use Exportable;
    protected $store;
    public function __construct(Store $store, $params)
    {
        $this->store = $store;
        $this->params = $params;
        $this->orientation = 'portrait';
    }
    public function exportData($type = null)
    {
        $dateRange = $this->getDeliveryDates();
        $params = $this->params;
        $params['dailyOrderNumbers'] = $this->store->modules->dailyOrderNumbers;
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
        // Disabled Old Workflow
        /*if (isset($dateRange['from'])) {
            $from = Carbon::parse($dateRange['from']);
            $orders = $orders->where(
                'delivery_date',
                '>=',
                $from->format('Y-m-d')
            );
        }
        if (isset($dateRange['to'])) {
            $to = Carbon::parse($dateRange['to']);
            $orders = $orders->where(
                'delivery_date',
                '<=',
                $to->format('Y-m-d')
            );
        }*/
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
                    return $mealOrder->order->delivery_date >=
                        $from->format('Y-m-d') &&
                        $mealOrder->order->delivery_date <=
                            $to->format('Y-m-d');
                });
            $customerMealOrders = $mealOrders->map(function ($mealOrder) {
                return [
                    'order_ID' => $mealOrder->order->order_number,
                    'order_placed' => $mealOrder->order->created_at->format(
                        'Y-m-d'
                    ),
                    'delivery_date' => $mealOrder->order->delivery_date->format(
                        'Y-m-d'
                    ),
                    'customer' =>
                        $mealOrder->order->user->details->firstname .
                        ' ' .
                        $mealOrder->order->user->details->lastname,
                    'address' => $mealOrder->order->user->details->address,
                    'city' => $mealOrder->order->user->details->city,
                    'state' => $mealOrder->order->user->details->state,
                    'zip' => $mealOrder->order->user->details->zip,
                    'delivery_instructions' =>
                        $mealOrder->order->user->details->delivery,
                    'meal_id' => $mealOrder->meal->title,
                    'meal_size' => $mealOrder->meal_size
                        ? $mealOrder->meal_size->title
                        : null,
                    // 'meal_components' => $mealOrder->components ? $mealOrder->components : null,
                    // 'meal_addons' => $mealOrder->addons ? $mealOrder->addons : null,
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
                'Address',
                'City',
                'State',
                'Zip',
                'Delivery Instructions',
                'Meal',
                'Size',
                // 'Components',
                // 'Addons',
                'Quantity',
                'Price'
            ]);
            return $customerMealOrders;
        } elseif ($type = 'pdf') {
            $customerOrders = $orders
                ->with(['meal_orders', 'lineItemsOrders'])
                ->get()
                ->groupBy('user_id')
                ->map(function ($orders, $userId) {
                    return [
                        'user' => User::find($userId),
                        'orders' => $orders->map(function ($order) {
                            return [
                                'id' => $order->id,
                                'order_number' => $order->order_number,
                                'address' => $order->user->userDetail->address,
                                'city' => $order->user->userDetail->city,
                                'state' => $order->user->userDetail->state,
                                'zip' => $order->user->userDetail->zip,
                                'delivery' =>
                                    $order->user->userDetail->delivery,
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
                                    [['Quantity', 'Size', 'Meal']], // Heading
                                    $order
                                        ->meal_orders()
                                        ->get()
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
                                                    $lineItemOrder->title,
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
            return $customerOrders->values();
        }
    }
    public function exportPdfView()
    {
        return 'reports.orders_by_customer_pdf';
    }
}
