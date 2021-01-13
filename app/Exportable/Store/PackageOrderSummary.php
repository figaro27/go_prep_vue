<?php
namespace App\Exportable\Store;
use App\Exportable\Exportable;
use App\Store;
use App\StoreModule;
use App\User;
use App\MealOrder;
use Illuminate\Support\Carbon;
use App\ReportRecord;
use App\MealPackageOrder;

class PackageOrderSummary
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
        $dateRange = $this->getDeliveryDates();
        $params = $this->params;
        $params['dailyOrderNumbers'] = $this->store->modules->dailyOrderNumbers;
        $params->date_format = $this->store->settings->date_format;

        $orders = $this->store->orders()->where(['paid' => 1, 'voided' => 0]);

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
                        ->whereHas('meal_package_orders', function (
                            $subquery1
                        ) use ($dateRange) {
                            $subquery1->whereNotNull(
                                'meal_package_orders.delivery_date'
                            );

                            if (isset($dateRange['from'])) {
                                $from = Carbon::parse($dateRange['from']);
                                $subquery1->where(
                                    'meal_package_orders.delivery_date',
                                    '>=',
                                    $from->format('Y-m-d')
                                );
                            }

                            if (isset($dateRange['to'])) {
                                $to = Carbon::parse($dateRange['to']);
                                $subquery1->where(
                                    'meal_package_orders.delivery_date',
                                    '<=',
                                    $to->format('Y-m-d')
                                );
                            }
                        });
                });
        });

        $orders = $orders->where(function ($order) {
            $order->whereHas('meal_package_orders');
        });

        if (isset($params['pickupLocationId'])) {
            $orders = $orders->where(
                'pickup_location_id',
                $params['pickupLocationId']
            );
        }

        if ($type === 'csv' || $type === 'xls') {
            $mealPackageOrders = MealPackageOrder::where(
                'store_id',
                $this->store->id
            )
                ->with(
                    'order',
                    'meal_package',
                    'meal_package_size',
                    'meal_orders'
                )
                ->get()
                ->filter(function ($mealPackageOrder) {
                    if (
                        $mealPackageOrder->order->paid === 0 ||
                        $mealPackageOrder->order->voided === 1
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

                    return !$mealPackageOrder->order->isMultipleDelivery
                        ? $mealPackageOrder->order->delivery_date >=
                                $from->format('Y-m-d') &&
                                $mealPackageOrder->order->delivery_date <=
                                    $to->addDays(1)->format('Y-m-d')
                        : $mealPackageOrder->delivery_date >=
                                $from->format('Y-m-d') &&
                                $mealPackageOrder->delivery_date <=
                                    $to->addDays(1)->format('Y-m-d');
                });

            $customerMealPackageOrders = $this->formatMealPackageOrders(
                $mealPackageOrders,
                $type
            );

            return $customerMealPackageOrders;
        } elseif ($type = 'pdf') {
            $customerOrders = $orders;

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
                                'phone' => $order->user->details->phone,
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
                                    [['Quantity', 'Size', 'Package', 'Items']], // Heading
                                    $order
                                        ->meal_package_orders()
                                        ->get()
                                        ->sortBy('delivery_date')
                                        ->map(function ($mealPackageOrder) {
                                            return [
                                                'quantity' =>
                                                    $mealPackageOrder->quantity ??
                                                    1,
                                                'size' => ($mealPackageOrder->customSize
                                                        ? $mealPackageOrder->customSize
                                                        : $mealPackageOrder->meal_package_size)
                                                    ? $mealPackageOrder
                                                        ->meal_package_size
                                                        ->title
                                                    : null,
                                                'title' =>
                                                    $mealPackageOrder->customTitle ??
                                                    $mealPackageOrder
                                                        ->meal_package->title,
                                                'items' =>
                                                    $mealPackageOrder->items_quantity
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
        return 'reports.package_order_summary_pdf';
    }

    public function formatMealPackageOrders($mealPackageOrders, $type)
    {
        // Special formatting for Eat Right

        if ($this->store->id === 196 || $this->store->id === 3) {
            $mealPackageOrders = $mealPackageOrders->map(function (
                $mealPackageOrder
            ) {
                return [
                    'customer' =>
                        $mealPackageOrder->order->user->details->firstname .
                        ' ' .
                        $mealPackageOrder->order->user->details->lastname,
                    'phone' => $mealPackageOrder->order->user->details->phone,
                    'quantity' => $mealPackageOrder->quantity,
                    'name' =>
                        $mealPackageOrder->customTitle ??
                        $mealPackageOrder->meal_package->title,
                    'items' => $mealPackageOrder->items_quantity,
                    'size' => ($mealPackageOrder->customSize
                            ? $mealPackageOrder->customSize
                            : $mealPackageOrder->meal_package_size)
                        ? $mealPackageOrder->meal_package_size->title
                        : null,
                    'transferType' => $mealPackageOrder->order->pickup
                        ? 'Pick Up'
                        : 'Delivery'
                ];
            });
        } else {
            $mealPackageOrders = $mealPackageOrders->map(function (
                $mealPackageOrder
            ) {
                return [
                    'order_ID' => $mealPackageOrder->order->order_number,
                    'order_placed' => $mealPackageOrder->order->created_at->format(
                        'Y-m-d'
                    ),
                    'delivery_date' => !$mealPackageOrder->order
                        ->isMultipleDelivery
                        ? $mealPackageOrder->order->delivery_date->format(
                            'Y-m-d'
                        )
                        : $mealPackageOrder->delivery_date->format('Y-m-d'),
                    'customer' =>
                        $mealPackageOrder->order->user->details->firstname .
                        ' ' .
                        $mealPackageOrder->order->user->details->lastname,
                    'email' => $mealPackageOrder->order->user->email,
                    'phone' => $mealPackageOrder->order->user->details->phone,
                    'address' =>
                        $mealPackageOrder->order->user->details->address,
                    'city' => $mealPackageOrder->order->user->details->city,
                    'state' => $mealPackageOrder->order->user->details->state,
                    'zip' => $mealPackageOrder->order->user->details->zip,
                    'delivery_instructions' =>
                        $mealPackageOrder->order->user->details->delivery,
                    'meal_id' =>
                        $mealPackageOrder->customTitle ??
                        $mealPackageOrder->meal_package->title,
                    'meal_size' => ($mealPackageOrder->customSize
                            ? $mealPackageOrder->customSize
                            : $mealPackageOrder->meal_package_size)
                        ? $mealPackageOrder->meal_package_size->title
                        : null,
                    'items' => $mealPackageOrder->items_quantity,
                    'special_instructions' =>
                        $mealPackageOrder->special_instructions,
                    'quantity' => $mealPackageOrder->quantity,
                    'price' =>
                        '$' .
                        number_format(
                            $mealPackageOrder->quantity *
                                $mealPackageOrder->price,
                            2
                        )
                ];
            });
        }

        $mealPackageOrders = $this->prepend($mealPackageOrders);

        return $mealPackageOrders;
    }

    public function prepend($mealPackageOrders)
    {
        // Special formatting for Eat Right

        if ($this->store->id === 196 || $this->store->id === 3) {
            $mealPackageOrders->prepend([
                'Customer Name',
                'Phone',
                'QTY',
                'Item Name',
                'Items',
                'Size',
                'Transfer Type'
            ]);
        } else {
            $mealPackageOrders->prepend([
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
                'Package',
                'Size',
                'Items',
                'Special Instructions',
                'Quantity',
                'Price'
            ]);
        }

        return $mealPackageOrders;
    }
}
