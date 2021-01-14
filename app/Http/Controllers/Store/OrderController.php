<?php

namespace App\Http\Controllers\Store;

use App\Order;
use App\Bag;
use App\Meal;
use App\MealOrder;
use App\MealPackageOrder;
use App\MealPackage;
use App\MealPackageSize;
use App\MealOrderComponent;
use App\MealOrderAddon;
use App\LineItem;
use App\LineItemOrder;
use App\MealAttachment;
use App\User;
use App\Customer;
use App\Card;
use App\OrderBag;
use App\OrderTransaction;
use App\PurchasedGiftCard;
use Illuminate\Http\Request;
use App\Http\Controllers\Store\StoreController;
use Illuminate\Support\Carbon;
use DB;
use App\Traits\DeliveryDates;
use Illuminate\Pagination\Paginator;
use App\Billing\Constants;
use App\Billing\Charge;
use App\Billing\Authorize;
use App\Billing\Billing;
use App\MealSize;
use App\Subscription;
use App\MealSubscription;
use App\Mail\Customer\NewGiftCard;

class OrderController extends StoreController
{
    use DeliveryDates;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $page = -1, $pageSize = -1)
    {
        if (!$this->store->has('orders')) {
            return [];
        }

        $hide = $request->query('hide', ['items']);
        // Start date is all future orders by default
        $start = $request->query('start', Carbon::today());
        $end = $request->query('end', null);
        $search = $request->query('query', null);
        $voided = $request->query('voided', null);
        $productionGroupId = $request->query('production_group_id', null);

        $query = $this->store
            ->orders()
            ->with(['user', 'pickup_location', 'purchased_gift_cards'])
            ->where(['paid' => 1])
            ->without($hide);

        if ($start) {
            $query = $query->whereDate('delivery_date', '>=', $start);
        }

        if ($end) {
            $query = $query->whereDate('delivery_date', '<=', $end);
        }

        // If multiple delivery days, get orders by looking at meal order dates
        if ($this->store->modules->multipleDeliveryDays) {
            $mealOrders = $this->store->mealOrders();
            if ($start) {
                $mealOrders = $mealOrders->where('delivery_date', '>=', $start);
            }

            if ($end) {
                $mealOrders = $mealOrders->where('delivery_date', '<=', $end);
            }

            $orderIds = $mealOrders->get()->map(function ($mealOrder) {
                return $mealOrder->order->id;
            });

            $query = $this->store
                ->orders()
                ->with(['user', 'pickup_location', 'purchased_gift_cards'])
                ->whereIn('id', $orderIds)
                ->where(['paid' => 1])
                ->without($hide);
        }

        if ($search) {
            $query = $query->whereLike(
                [
                    'order_number',
                    'dailyOrderNumber',
                    'user.details.firstname',
                    'user.details.lastname',
                    'user.details.address',
                    'user.details.zip'
                ],
                $search
            );
        }

        if (!is_null($productionGroupId)) {
            $query = $query->where(
                'production_group_id',
                (int) $productionGroupId
            );
        }

        if (!is_null($voided)) {
            $query = $query->where('voided', (int) $voided);
        }

        if ($page === -1) {
            return $query->get();
        }

        // Paginate
        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });
        return $query->paginate($pageSize);
    }

    public function getUpcomingOrders()
    {
        return [];
        $fromDate = Carbon::today(
            $this->store->settings->timezone
        )->startOfDay();

        $data = [];
        if ($this->store->has('orders')) {
            $orders = $this->store
                ->orders()
                ->with(['user', 'pickup_location', 'meal_orders']);

            $orders = $orders->where(function ($query) use ($fromDate) {
                $query
                    ->where(function ($query1) use ($fromDate) {
                        $query1
                            ->where('isMultipleDelivery', 0)
                            ->where('paid', 1);
                        $query1->where(
                            'delivery_date',
                            '>=',
                            $fromDate->format('Y-m-d')
                        );
                    })
                    ->orWhere(function ($query2) use ($fromDate) {
                        $query2
                            ->where('isMultipleDelivery', 1)
                            ->where('paid', 1)
                            ->whereHas('meal_orders', function ($subquery) use (
                                $fromDate
                            ) {
                                $subquery->whereNotNull(
                                    'meal_orders.delivery_date'
                                );
                                $subquery->where(
                                    'meal_orders.delivery_date',
                                    '>=',
                                    $fromDate->format('Y-m-d')
                                );
                            });
                    });
            });

            $data = $orders->get();

            // Newly Added for Table Data
            // if ($data) {
            //     foreach ($data as &$order) {
            //         $newItems = [];
            //         $mealOrders = $order
            //             ->meal_orders()
            //             ->with('meal')
            //             ->get();

            //         if ($order->isMultipleDelivery) {
            //             if ($mealOrders) {
            //                 foreach ($mealOrders as $mealOrder) {
            //                     if (!$mealOrder->delivery_date) {
            //                         continue;
            //                     }

            //                     $mealOrder_date = Carbon::parse(
            //                         $mealOrder->delivery_date
            //                     )->format('Y-m-d');
            //                     if (
            //                         $mealOrder_date < $fromDate->format('Y-m-d')
            //                     ) {
            //                         continue;
            //                     }

            //                     $newItems[] = $mealOrder;
            //                 }
            //             }
            //         } else {
            //             $newItems = $mealOrders;
            //         }

            //         $order->newItems = $newItems;
            //     }
            // }
        }

        return $data;

        /*return $this->store->has('orders')
            ? $this->store
                ->orders()
                ->with(['user', 'pickup_location'])
                ->where(['paid' => 1])
                ->where('delivery_date', '>=', $fromDate)
                ->get()
            : [];*/
    }

    public function getUpcomingOrdersWithoutItems()
    {
        return [];

        // Optimized orders for Store/Orders & Store/Payments pages
        $fromDate = Carbon::today(
            $this->store->settings->timezone
        )->startOfDay();

        if ($this->store->has('orders')) {
            $orders = $this->store
                ->orders()
                ->with(['pickup_location'])
                ->where(['paid' => 1]);
            $orders = $orders->where(function ($query) use ($fromDate) {
                $query
                    ->where(function ($query1) use ($fromDate) {
                        $query1->where('isMultipleDelivery', 0);
                        $query1->where(
                            'delivery_date',
                            '>=',
                            $fromDate->format('Y-m-d')
                        );
                    })
                    ->orWhere(function ($query2) use ($fromDate) {
                        $query2
                            ->where('isMultipleDelivery', 1)
                            ->whereHas('meal_orders', function ($subquery) use (
                                $fromDate
                            ) {
                                $subquery->whereNotNull(
                                    'meal_orders.delivery_date'
                                );
                                $subquery->where(
                                    'meal_orders.delivery_date',
                                    '>=',
                                    $fromDate->format('Y-m-d')
                                );
                            });
                    });
            });
            $orders = $orders->get();

            // Disabled Workflow
            /*$orders = $this->store
                  ->orders()
                  ->with(['pickup_location'])
                  ->where(['paid' => 1])
                  ->whereDate('delivery_date', '>=', $fromDate)
                  // ->whereDate('delivery_date', '<=', $fromDate->addWeeks(2))
                  ->get();*/

            $orders->makeHidden([
                'user',
                'items',
                'meal_ids',
                'line_items_order',
                'meal_package_items',

                'added_by_store_id',
                'chargedAmount',
                'currency',
                'order_day',
                'originalAmount',
                'payment_gateway',
                'paid',
                'paid_at',
                'pickup_location',
                'pickup_location_id',
                'purchasedGiftCardReduction',
                'purchased_gift_card_code',
                'purchased_gift_card_id',
                'stripe_id',
                'transferTime',
                'user_id'
            ]);

            if (!$this->store->modules->multipleDeliveryDays) {
                $orders->makeHidden([
                    'delivery_dates_array',
                    'isMultipleDelivery'
                ]);
            }
            return $orders;
        }

        return [];
    }

    public function getOrdersToday(Request $request)
    {
        $paymentsPage = $request->get('payments');
        $removeManualOrders = $request->get('removeManualOrders');
        $removeCashOrders = $request->get('removeCashOrders');

        $fromDate = Carbon::today(
            $this->store->settings->timezone
        )->startOfDay();

        $date = '';
        if ($paymentsPage) {
            $date = 'created_at';
        } else {
            $date = 'delivery_date';
        }

        $orders = $this->store->has('orders')
            ? $this->store
                ->orders()
                ->with(['user', 'pickup_location'])
                ->where(['paid' => 1])
                ->where($date, '>=', $fromDate)
            : [];

        if (isset($removeManualOrders) && $removeManualOrders === 1) {
            $orders = $orders->where('manual', 0);
        }

        if (isset($removeCashOrders) && $removeCashOrders === 1) {
            $orders = $orders->where('cashOrder', 0);
        }

        $orders = $orders->get();

        $orders->makeHidden([
            'items',
            'meal_ids',
            'line_items_order',
            'meal_package_items'
        ]);
        return $orders;
    }

    public function getFulfilledOrders()
    {
        return $this->store->has('orders')
            ? $this->store
                ->orders()
                ->with(['user', 'pickup_location'])
                ->where(['paid' => 1, 'fulfilled' => 1])
                ->get()
            : [];
    }

    public function getOrdersWithDates(Request $request)
    {
        $paymentsPage = $request->get('payments');

        if ($request->get('end') != null) {
            $endDate = $request->get('end');
        } else {
            $endDate = $request->get('start');
        }

        $startDate = Carbon::parse($request->get('start'))->format('Y-m-d');
        $endDate = Carbon::parse($endDate)->format('Y-m-d');

        $data = [];

        if ($paymentsPage) {
            $data = $this->store->has('orders')
                ? $this->store
                    ->orders()
                    ->with(['user', 'pickup_location'])
                    ->where(['paid' => 1])
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<=', $endDate)
                    ->get()
                : [];
        } else {
            if ($this->store->has('orders')) {
                $orders = $this->store
                    ->orders()
                    ->with(['user', 'pickup_location']);

                $orders = $orders->where(function ($query) use (
                    $startDate,
                    $endDate
                ) {
                    $query->where(function ($query) use ($startDate, $endDate) {
                        $query
                            ->where('isMultipleDelivery', 0)
                            ->where('paid', 1);
                        $query->where('delivery_date', '>=', $startDate);
                        $query->where('delivery_date', '<=', $endDate);
                    });
                });

                $data = $orders->get();
            }
        }

        return $data;
    }

    public function getOrdersWithDatesWithoutItems(Request $request)
    {
        // Optimized orders for Store/Orders & Store/Payments pages

        $paymentsPage = $request->get('payments');
        $removeManualOrders = $request->get('removeManualOrders');
        $removeCashOrders = $request->get('removeCashOrders');

        $startDate = $request->get('start')
            ? $request->get('start')
            : date('Y-m-d');

        if ($request->get('end') != null) {
            $endDate = $request->get('end')
                ? $request->get('end')
                : date('Y-m-d', strtotime($startDate . ' + 7 days'));
        } else {
            $endDate = $request->get('start')
                ? $request->get('start')
                : date('Y-m-d', strtotime($startDate . ' + 7 days'));
        }

        $date = '';
        if ($paymentsPage) {
            $date = 'created_at';
        } else {
            $date = 'delivery_date';
        }

        $orders = $this->store->has('orders')
            ? $this->store
                ->orders()
                ->with(['user', 'pickup_location'])
                ->where(['paid' => 1])
                ->where($date, '>=', $startDate)
                ->where($date, '<=', $endDate)
            : [];

        if (isset($removeManualOrders) && $removeManualOrders === 1) {
            $orders = $orders->where('manual', 0);
        }

        if (isset($removeCashOrders) && $removeCashOrders === 1) {
            $orders = $orders->where('cashOrder', 0);
        }

        $orders = $orders->get();

        $orders->makeHidden([
            'items',
            'meal_ids',
            'line_items_order',
            'meal_package_items'
        ]);

        return $orders;
    }

    public function getMealOrdersWithDates(Request $request)
    {
        if ($request->get('start')) {
            $startDate = Carbon::parse($request->get('start'))->format('Y-m-d');
        } else {
            $startDate = Carbon::now()
                ->startOfDay()
                ->toDateTimeString();
        }

        if ($request->get('end')) {
            $endDate = $request->get('end');
        } else {
            $endDate = Carbon::parse($startDate)
                ->addWeeks(1)
                ->toDateTimeString();
        }

        $startTime = $request->get('transferTimeStart');

        $endTime = $request->get('transferTimeEnd');

        // $orders = $this->store->has('orders')
        //     ? $this->store
        //         ->orders()
        //         ->where(['paid' => 1, 'voided' => 0, 'isMultipleDelivery' => 0])
        //         ->where('delivery_date', '>=', $startDate)
        //         ->where('delivery_date', '<=', $endDate)
        //         ->where('transferTime', '>=', $startTime)
        //         ->where('transferTime', '<=', $endTime)
        //         ->get()
        //         ->map(function ($order) {
        //             return $order->id;
        //         })
        //     : [];

        $orders = $this->store
            ->orders()
            ->where(['paid' => 1, 'voided' => 0, 'isMultipleDelivery' => 0])
            ->where('delivery_date', '>=', $startDate)
            ->where('delivery_date', '<=', $endDate)
            ->get();

        if ($startTime) {
            $startTime = Carbon::parse($startTime)
                ->subMinutes('1')
                ->format('H:i:s');
            $orders = $orders->filter(function ($order) use ($startTime) {
                $transferTime = Carbon::parse(
                    substr($order->transferTime, 0, 8)
                )->format('H:i:s');
                if ($transferTime >= $startTime) {
                    return $order;
                }
            });
        }

        if ($endTime) {
            $endTime = Carbon::parse($endTime)
                ->addMinutes('1')
                ->format('H:i:s');
            $orders = $orders->filter(function ($order) use ($endTime) {
                $transferTime = Carbon::parse(
                    substr($order->transferTime, 0, 8)
                )->format('H:i:s');
                if ($transferTime <= $endTime) {
                    return $order;
                }
            });
        }

        $orders = $orders->map(function ($order) {
            return $order->id;
        });

        // Meal Orders

        $mealOrders = MealOrder::whereIn('order_id', $orders)->get();

        $mealQuantities = [];

        foreach ($mealOrders as $i => $mealOrder) {
            $title = $mealOrder->base_title_without_date;
            $size = $mealOrder->base_size;
            $title = $title . '<sep>' . $size;

            if (!isset($mealQuantities[$title])) {
                $mealQuantities[$title] = 0;
            }

            $mealQuantities[$title] += $mealOrder->quantity;
        }

        $production = [];

        foreach ($mealQuantities as $title => $quantity) {
            $temp = explode('<sep>', $title);
            $size = $temp && isset($temp[1]) ? $temp[1] : "";
            $title = $temp[0];
            $row = [];
            $row['base_title'] = $title;
            $row['base_size'] = $size;
            $row['quantity'] = $quantity;
            array_push($production, $row);
        }

        // Line Items

        $lineItemOrders = LineItemOrder::whereIn('order_id', $orders)->get();

        $lineItemQuantities = [];

        foreach ($lineItemOrders as $i => $lineItemOrder) {
            $title = $lineItemOrder->title;
            $size = $lineItemOrder->size;
            $title = $title . '<sep>' . $size;

            if (!isset($lineItemQuantities[$title])) {
                $lineItemQuantities[$title] = 0;
            }

            $lineItemQuantities[$title] += $lineItemOrder->quantity;
        }

        foreach ($lineItemQuantities as $title => $quantity) {
            $temp = explode('<sep>', $title);
            $size = $temp && isset($temp[1]) ? $temp[1] : "";
            $title = $temp[0];
            $row = [];
            $row['base_title'] = $title;
            $row['base_size'] = $size;
            $row['quantity'] = $quantity;
            array_push($production, $row);
        }

        usort($production, function ($a, $b) {
            return strcmp($b['base_title'], $a['base_title']);
        });

        return $production;
    }

    // For multiple delivery
    public function getMealOrdersWithDatesMD(Request $request)
    {
        if ($request->get('end') != null) {
            $endDate = $request->get('end');
        } else {
            $endDate = $request->get('start');
        }

        $startDate = Carbon::parse($request->get('start'))->format('Y-m-d');
        $endDate = Carbon::parse($endDate)->format('Y-m-d');

        $mealOrders = MealOrder::where('delivery_date', '>=', $startDate)
            ->where('delivery_date', '<=', $endDate)
            ->where('store_id', $this->store->id)
            ->whereHas('order', function ($order) {
                $order->where('paid', 1)->where('voided', 0);
            })
            ->get();

        $mealQuantities = [];

        foreach ($mealOrders as $i => $mealOrder) {
            $title = $mealOrder->base_title_without_date;
            $size = $mealOrder->base_size;
            $title = $title . '<sep>' . $size;

            if (!isset($mealQuantities[$title])) {
                $mealQuantities[$title] = 0;
            }

            $mealQuantities[$title] += $mealOrder->quantity;
        }

        $production = [];

        foreach ($mealQuantities as $title => $quantity) {
            $temp = explode('<sep>', $title);
            $size = $temp && isset($temp[1]) ? $temp[1] : "";
            $title = $temp[0];
            $row = [];
            $row['base_title'] = $title;
            $row['base_size'] = $size;
            $row['quantity'] = $quantity;
            array_push($production, $row);
        }

        usort($production, function ($a, $b) {
            return strcmp($b['base_title'], $a['base_title']);
        });

        return $production;
    }

    public function getLatestOrder()
    {
        $orders = $this->store->has('orders') ? $this->store->orders() : [];
        return $orders->orderBy('created_at', 'desc')->first();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = $this->store
            ->orders()
            ->with([
                'user.userDetail',
                'meals',
                'pickup_location',
                'lineItemsOrder',
                'purchased_gift_cards'
            ])
            ->where('id', $id)
            ->first();

        $order->makeHidden([
            'meals',
            'meal_ids',
            'payment_gateway',
            'paid',
            'pickup_location',
            'stripe_id',
            'user_id',
            'visible_items'
        ]);

        if (!$this->store->modules->multipleDeliveryDays) {
            $order->makeHidden(['delivery_dates_array', 'isMultipleDelivery']);
        }

        return $order;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return Order::updateOrder($id, $request->all());
    }

    public function getReservedStock($meal)
    {
        // Accounts for meal stock in upcoming renewals of subscriptions
        $mealSubs = MealSubscription::where('meal_id', $meal->id)->get();
        $quantity = 0;
        foreach ($mealSubs as $mealSub) {
            $sub = Subscription::where(
                'id',
                $mealSub->subscription_id
            )->first();
            if ($sub->status == 'active' && $sub->renewalCount > 0) {
                $quantity += $mealSub->quantity;
            }
        }
        return $quantity;
    }

    public function adjustOrder(Request $request)
    {
        try {
            $order = Order::where('id', $request->get('orderId'))->first();
            $store = $order->store;
            $bagItems = $request->get('bag');
            $bag = new Bag($bagItems, $store);
            // Checking all meals are in stock before proceeding
            if ($this->store->modules->stockManagement) {
                foreach ($bag->getItems() as $item) {
                    $meal = Meal::where('id', $item['meal']['id'])->first();
                    if ($meal && $meal->stock !== null) {
                        $reservedStock = $this->getReservedStock($meal);
                        $existingMealOrder = $order
                            ->meal_orders()
                            ->where('meal_id', $item['meal']['id'])
                            ->first();
                        $quantity = $existingMealOrder
                            ? $existingMealOrder->quantity
                            : 0;
                        if (
                            $meal->stock + $quantity <
                            $item['quantity'] + $reservedStock
                        ) {
                            $stockLeft = $meal->stock - $reservedStock;
                            if ($stockLeft < 0) {
                                $stockLeft = 0;
                            }
                            return response()->json(
                                [
                                    'message' =>
                                        $meal->title .
                                        ' currently has ' .
                                        $stockLeft .
                                        ' left in stock. Please adjust your order and try again.'
                                ],
                                400
                            );
                        }
                        $meal->stock -= $item['quantity'] - $quantity;
                        if (
                            $meal->stock === 0 ||
                            $meal->stock <= $this->getReservedStock($meal)
                        ) {
                            $meal->lastOutOfStock = date('Y-m-d H:i:s');
                            $meal->active = 0;
                            Subscription::syncStock($meal);
                        }
                        $meal->update();
                    }
                }
            }

            $couponId = $request->get('coupon_id');
            $couponReduction = $request->get('couponReduction');
            $couponCode = $request->get('couponCode');
            $purchasedGiftCardId = $request->get('purchased_gift_card_id');
            $purchasedGiftCardReduction = $request->get(
                'purchasedGiftCardReduction'
            )
                ? $request->get('purchasedGiftCardReduction')
                : 0;
            $promotionReduction = $request->get('promotionReduction');
            $pointsReduction = $request->get('pointsReduction');
            $deliveryFee = $request->get('deliveryFee');
            $deliveryDate = $request->get('deliveryDate');
            $isMultipleDelivery = (int) $request->get('isMultipleDelivery');
            $pickupLocation = $request->get('pickupLocation');
            $transferTime = $request->get('transferTime');
            $bagTotal = $bag->getTotal() + $request->get('lineItemTotal');
            $subtotal = $request->get('subtotal');
            $preFeePreDiscount = $request->get('subtotal');
            $afterDiscountBeforeFees = $request->get('afterDiscount');
            $processingFee = $request->get('processingFee');
            $mealPlanDiscount = $request->get('mealPlanDiscount');
            $salesTax = $request->get('salesTax');
            $deliveryFee = $request->get('deliveryFee');
            $processingFee = $request->get('processingFee');
            $cashOrder = $request->get('cashOrder');
            $gratuity = $request->get('gratuity');
            $grandTotal = $request->get('grandTotal');
            $adjustedDifference = $request->get('grandTotal') - $order->amount;

            $customer = Customer::where('id', $order->customer_id)->first();
            $customer->total_paid += $adjustedDifference;
            $customer->update();

            $balance = $request->get('dontAffectBalance')
                ? 0
                : $request->get('grandTotal') - $order->amount;
            $customSalesTax =
                $request->get('customSalesTax') !== null
                    ? $request->get('customSalesTax')
                    : 0;

            $hot = $request->get('hot');
            // $deposit =
            //     (($order->deposit * $order->amount) / 100 / $grandTotal) * 100;
            $originalDeliveryDate = $order->delivery_date;
            $coolerDeposit = $request->get('coolerDeposit');

            $order->delivery_date = $deliveryDate;
            $order->transferTime = $request->get('transferTime');
            $order->adjusted = 1;
            $order->pickup = $request->get('pickup');
            $order->shipping = $request->get('shipping');
            $order->preFeePreDiscount = $preFeePreDiscount;
            $order->mealPlanDiscount = $mealPlanDiscount;
            $order->afterDiscountBeforeFees = $afterDiscountBeforeFees;
            $order->deliveryFee = $deliveryFee;
            $order->processingFee = $processingFee;
            $order->isMultipleDelivery = $isMultipleDelivery;
            $order->salesTax = $salesTax;
            $order->customSalesTax = $customSalesTax;
            $order->gratuity = $gratuity;
            $order->amount = $grandTotal;
            // $order->deposit = $deposit;
            $order->adjustedDifference += $adjustedDifference;
            $order->balance += $balance;
            $order->coupon_id = $couponId;
            $order->couponReduction = $couponReduction;
            $order->couponCode = $couponCode;
            $order->coupon_id = $couponId;
            $order->promotionReduction = $promotionReduction;
            $order->pointsReduction = $pointsReduction;
            $order->couponCode = $couponCode;
            $order->purchased_gift_card_id = $purchasedGiftCardId;
            $order->purchasedGiftCardReduction = $purchasedGiftCardReduction;
            $order->pickup_location_id = $pickupLocation;
            $order->transferTime = $transferTime;
            $order->hot = $hot;
            $order->coolerDeposit = $coolerDeposit;

            $dailyOrderNumber = 0;
            if (!$isMultipleDelivery) {
                $max = Order::where('store_id', $store->id)
                    ->whereDate('delivery_date', $deliveryDate)
                    ->max('dailyOrderNumber');
                $dailyOrderNumber = $max + 1;

                if ($originalDeliveryDate != $deliveryDate) {
                    $order->dailyOrderNumber = $dailyOrderNumber;
                }
            }

            $order->save();

            $order->meal_orders()->delete();
            $order->meal_package_orders()->delete();
            $order->lineItemsOrder()->delete();
            foreach ($bag->getItems() as $item) {
                if (
                    isset($item['meal']['gift_card']) &&
                    $item['meal']['gift_card']
                ) {
                    $quantity = $item['quantity'];

                    for ($i = 0; $i < $quantity; $i++) {
                        $purchasedGiftCard = new PurchasedGiftCard();
                        $purchasedGiftCard->store_id = $store->id;
                        $purchasedGiftCard->gift_card_id = $item['meal']['id'];
                        $purchasedGiftCard->user_id = $order->user->id;
                        $purchasedGiftCard->order_id = $order->id;
                        $purchasedGiftCard->code = strtoupper(
                            substr(uniqid(rand(10, 99), false), 0, 6) .
                                chr(rand(65, 90)) .
                                rand(10, 99)
                        );
                        $purchasedGiftCard->amount = isset(
                            $item['meal']['value']
                        )
                            ? $item['meal']['value']
                            : $item['meal']['price'];
                        $purchasedGiftCard->balance = isset(
                            $item['meal']['value']
                        )
                            ? $item['meal']['value']
                            : $item['meal']['price'];
                        $purchasedGiftCard->emailRecipient = isset(
                            $item['emailRecipient']
                        )
                            ? $item['emailRecipient']
                            : null;
                        $purchasedGiftCard->save();

                        if (isset($item['emailRecipient'])) {
                            $store->sendNotification('new_gift_card', [
                                'order' => $order ?? null,
                                'purchasedGiftCard' => $purchasedGiftCard,
                                'emailRecipient' => $item['emailRecipient']
                            ]);
                        }
                    }
                }
                $mealOrder = new MealOrder();
                $mealOrder->order_id = $order->id;
                $mealOrder->store_id = $store->id;
                $mealOrder->meal_id = $item['meal']['id'];
                $mealOrder->quantity = $item['quantity'];
                $mealOrder->price = $item['price'] * $item['quantity'];
                if (!$item['meal_package']) {
                    $mealOrder->customTitle = isset($item['customTitle'])
                        ? $item['customTitle']
                        : null;
                    $mealOrder->customSize = isset($item['customSize'])
                        ? $item['customSize']
                        : null;
                }
                if (isset($item['delivery_day']) && $item['delivery_day']) {
                    $mealOrder->delivery_date =
                        $item['delivery_day']['day_friendly'];
                }
                if (isset($item['size']) && $item['size']) {
                    $mealOrder->meal_size_id = $item['size']['id'];
                }
                if (isset($item['special_instructions'])) {
                    $mealOrder->special_instructions =
                        $item['special_instructions'];
                }
                if (isset($item['free'])) {
                    $mealOrder->free = $item['free'];
                }
                if ($item['meal_package']) {
                    $mealOrder->meal_package = $item['meal_package'];
                    $mealOrder->added_price = isset($item['added_price'])
                        ? $item['added_price'] * $item['quantity']
                        : 0;
                }

                if (isset($item['meal_package_title'])) {
                    $mealOrder->meal_package_title =
                        $item['meal_package_title'];
                }

                if ($item['meal_package'] === true) {
                    if (
                        MealPackageOrder::where([
                            'meal_package_id' => $item['meal_package_id'],
                            'meal_package_size_id' =>
                                $item['meal_package_size_id'],
                            'order_id' => $order->id,
                            'delivery_date' =>
                                $item['delivery_day']['day_friendly'],
                            'customTitle' => $item['customTitle'],
                            'mappingId' => isset($item['mappingId'])
                                ? $item['mappingId']
                                : null
                        ])
                            ->get()
                            ->count() === 0
                    ) {
                        $mealPackageOrder = new MealPackageOrder();
                        $mealPackageOrder->store_id = $store->id;
                        $mealPackageOrder->order_id = $order->id;
                        $mealPackageOrder->meal_package_id =
                            $item['meal_package_id'];
                        $mealPackageOrder->meal_package_size_id =
                            $item['meal_package_size_id'];
                        $mealPackageOrder->quantity = $item['package_quantity'];
                        $mealPackageOrder->price = $item['package_price'];
                        if (
                            isset($item['delivery_day']) &&
                            $item['delivery_day']
                        ) {
                            $mealPackageOrder->delivery_date =
                                $item['delivery_day']['day_friendly'];
                        }
                        $mealPackageOrder->customTitle = isset(
                            $item['customTitle']
                        )
                            ? $item['customTitle']
                            : null;
                        $mealPackageOrder->customSize = isset(
                            $item['customSize']
                        )
                            ? $item['customSize']
                            : null;
                        $mealPackageOrder->mappingId = isset($item['mappingId'])
                            ? $item['mappingId']
                            : null;
                        $mealPackageOrder->category_id = isset(
                            $item['category_id']
                        )
                            ? $item['category_id']
                            : null;
                        $mealPackageOrder->items_quantity +=
                            $mealOrder->quantity;
                        $mealPackageOrder->save();

                        $mealOrder->meal_package_order_id =
                            $mealPackageOrder->id;
                    } else {
                        $mealOrder->meal_package_order_id = MealPackageOrder::where(
                            [
                                'meal_package_id' => $item['meal_package_id'],
                                'meal_package_size_id' =>
                                    $item['meal_package_size_id'],
                                'order_id' => $order->id,
                                'delivery_date' =>
                                    $item['delivery_day']['day_friendly'],
                                'customTitle' => $item['customTitle'],
                                'mappingId' => isset($item['mappingId'])
                                    ? $item['mappingId']
                                    : null
                            ]
                        )
                            ->pluck('id')
                            ->first();

                        $mealPackageOrder->items_quantity +=
                            $mealOrder->quantity;
                        $mealPackageOrder->update();
                    }
                    $mealOrder->price = $item['price'];
                }
                $mealOrder->category_id = isset($item['meal']['category_id'])
                    ? $item['meal']['category_id']
                    : null;
                $mealOrder->save();

                if (isset($item['components']) && $item['components']) {
                    foreach ($item['components'] as $componentId => $choices) {
                        foreach ($choices as $optionId) {
                            MealOrderComponent::create([
                                'meal_order_id' => $mealOrder->id,
                                'meal_component_id' => $componentId,
                                'meal_component_option_id' => $optionId
                            ]);
                        }
                    }
                }

                if (isset($item['addons']) && $item['addons']) {
                    foreach ($item['addons'] as $addonId) {
                        MealOrderAddon::create([
                            'meal_order_id' => $mealOrder->id,
                            'meal_addon_id' => $addonId
                        ]);
                    }
                }

                $attachments = MealAttachment::where([
                    'meal_id' => $item['meal']['id'],
                    'applyToAll' => 1
                ])->get();

                $explicitAttachments = MealAttachment::where([
                    'applyToAll' => 0,
                    'meal_id' => $item['meal']['id'],
                    'meal_size_id' => isset($item['size']['id'])
                        ? $item['size']['id']
                        : null
                ])->get();

                foreach ($explicitAttachments as $explicitAttachment) {
                    $attachments->push($explicitAttachment);
                }

                if ($attachments) {
                    foreach ($attachments as $attachment) {
                        $mealOrder = new MealOrder();
                        $mealOrder->order_id = $order->id;
                        $mealOrder->store_id = $store->id;
                        $mealOrder->meal_id = $attachment->attached_meal_id;
                        $mealOrder->meal_size_id =
                            $attachment->attached_meal_size_id;
                        $mealOrder->quantity =
                            $attachment->quantity * $item['quantity'];
                        $mealOrder->attached = 1;
                        $mealOrder->free = 1;
                        $mealOrder->hidden = $attachment->hidden;
                        if (
                            isset($item['delivery_day']) &&
                            $item['delivery_day']
                        ) {
                            $mealOrder->delivery_date =
                                $item['delivery_day']['day_friendly'];
                        }
                        $mealOrder->save();
                    }
                }
            }

            $lineItemsOrder = $request->get('lineItemsOrder');
            if ($lineItemsOrder != null) {
                foreach ($lineItemsOrder as $lineItemOrder) {
                    $newLineItemOrder = new LineItemOrder();
                    $newLineItemOrder->store_id = $store->id;
                    $newLineItemOrder->line_item_id = isset(
                        $lineItemOrder['line_item']
                    )
                        ? $lineItemOrder['line_item']['id']
                        : $lineItemOrder['id'];
                    $newLineItemOrder->order_id = $order->id;
                    $newLineItemOrder->quantity = $lineItemOrder['quantity'];
                    $newLineItemOrder->save();
                }
            }

            // if ($bagItems && count($bagItems) > 0) {
            //     OrderBag::where('order_id', (int) $order->id)->delete();

            //     foreach ($bagItems as $bagItem) {
            //         $orderBag = new OrderBag();
            //         $orderBag->order_id = (int) $order->id;
            //         $orderBag->bag = json_encode($bagItem);
            //         $orderBag->save();
            //     }
            // }

            // Send email to store only if it's not a manual order. Subject to change in future.
            if ($order->manual === 0) {
                try {
                    $store->sendNotification('adjusted_order', [
                        'order' => $order ?? null,
                        'pickup' => $order->pickup ?? null
                    ]);
                } catch (\Exception $e) {
                }
            }

            // Send email to customer
            $customerUser = User::where('id', $order->user_id)->first();
            if ($request->get('emailCustomer')) {
                try {
                    $customerUser->sendNotification('adjusted_order', [
                        'order' => $order ?? null,
                        'pickup' => $pickup ?? null
                    ]);
                } catch (\Exception $e) {
                }
            }

            return $order->id;
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => $e->getMessage()
                ],
                400
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }

    public function updateViewed()
    {
        Order::where('viewed', 0)->update(['viewed' => 1]);
    }

    public function charge(Request $request)
    {
        $orderId = $request->get('orderId');
        $chargeAmount = $request->get('chargeAmount');
        $order = Order::where('id', $orderId)->first();
        $cashOrder = $order->cashOrder;
        $store = $this->store;
        $application_fee = $store->settings->application_fee;
        $applyToBalance = $request->get('applyToBalance');

        $user = User::where('id', $order->user_id)->first();
        $customer = Customer::where('id', $order->customer_id)->first();

        if (!$cashOrder) {
            $cardId = $order->card_id;
            $card = Card::where('id', $cardId)->first();
        }

        if (!$card) {
            return response()->json(
                [
                    'message' =>
                        'The customer has deleted the card associated with this order from the system and the card can no longer be charged.'
                ],
                400
            );
        }

        $gateway = $card->payment_gateway;
        $storeSettings = $this->store->settings;
        $customer = $user->getStoreCustomer(
            $store->id,
            $storeSettings->currency,
            $gateway
        );

        if (!$cashOrder) {
            if ($gateway === Constants::GATEWAY_STRIPE) {
                $storeSource = \Stripe\Source::create(
                    [
                        "customer" => $user->stripe_id,
                        "original_source" => $card->stripe_id,
                        "usage" => "single_use"
                    ],
                    ["stripe_account" => $store->settings->stripe_id]
                );

                $charge = \Stripe\Charge::create(
                    [
                        "amount" => round(100 * $chargeAmount),
                        "currency" => $storeSettings->currency,
                        "source" => $storeSource,
                        // Change to "application_fee_amount" as per Stripe's updates
                        "application_fee" => round(
                            $chargeAmount * $application_fee
                        )
                    ],
                    ["stripe_account" => $store->settings->stripe_id],
                    [
                        "idempotency_key" =>
                            substr(uniqid(rand(10, 99), false), 0, 14) .
                            chr(rand(65, 90)) .
                            rand(0, 9)
                    ]
                );
            } elseif ($gateway === Constants::GATEWAY_AUTHORIZE) {
                $billing = Billing::init($gateway, $store);

                $charge = new \App\Billing\Charge();
                $charge->amount = round(100 * $chargeAmount);
                $charge->customer = $customer;
                $charge->card = $card;

                $transactionId = $billing->charge($charge);
                $charge->id = $transactionId;
            }
        }

        $order->chargedAmount += $chargeAmount;

        if ($applyToBalance) {
            $order->balance -= $chargeAmount;
        }
        $order->save();

        $order_transaction = new OrderTransaction();
        $order_transaction->order_id = $order->id;
        $order_transaction->store_id = $store->id;
        $order_transaction->user_id = $user->id;
        $order_transaction->customer_id = $customer->id;
        $order_transaction->type = 'charge';
        if (!$cashOrder) {
            $order_transaction->stripe_id = $charge->id;
            $order_transaction->card_id = $cardId;
        } else {
            $order_transaction->stripe_id = null;
            $order_transaction->card_id = null;
        }
        $order_transaction->amount = $chargeAmount;
        $order_transaction->applyToBalance = $applyToBalance;
        $order_transaction->save();

        return 'Charged $' . $chargeAmount;
    }

    public function refundOrder(Request $request)
    {
        $order = Order::where('id', $request->get('orderId'))->first();
        $store = $this->store;
        $user = User::where('id', $order->user_id)->first();
        $customer = Customer::where('id', $order->customer_id)->first();
        $applyToBalance = $request->get('applyToBalance');
        $cooler = $request->get('cooler');

        $originalAmount = $order->originalAmount;
        $chargedAmount = $order->chargedAmount;
        $totalCharged = $originalAmount + $chargedAmount;
        $refundAmount = $request->get('refundAmount');
        if ($refundAmount === null) {
            $refundAmount = $totalCharged;
        }
        if ($refundAmount > $totalCharged) {
            return 1;
        }

        $difference = 0;

        if ($refundAmount > $originalAmount) {
            $difference = $refundAmount - $originalAmount;
            $refundAmount = $originalAmount;
        }

        $card = Card::where('id', $order->card_id)->first();
        $refund = \Stripe\Refund::create(
            [
                'charge' => $order->stripe_id,
                'amount' => $refundAmount * 100
            ],
            ["stripe_account" => $this->store->settings->stripe_id]
        );

        $order_transaction = new OrderTransaction();
        $order_transaction->order_id = $order->id;
        $order_transaction->store_id = $store->id;
        $order_transaction->user_id = $user->id;
        $order_transaction->customer_id = $customer->id;
        $order_transaction->type = 'refund';
        $order_transaction->stripe_id = $refund->id;
        $order_transaction->card_id = $card ? $card->id : null;
        $order_transaction->amount = $refundAmount;
        $order_transaction->applyToBalance = $applyToBalance;
        $order_transaction->save();

        if ($difference > 0) {
            $charges = OrderTransaction::where([
                'order_id' => $order->id,
                'type' => 'charge'
            ])->get();
            foreach ($charges as $charge) {
                if ($difference <= $charge->amount) {
                    $card = Card::where('id', $charge->card_id)->first();
                    $refund = \Stripe\Refund::create(
                        [
                            'charge' => $charge->stripe_id,
                            'amount' => $difference * 100
                        ],
                        ["stripe_account" => $this->store->settings->stripe_id]
                    );

                    $order_transaction = new OrderTransaction();
                    $order_transaction->order_id = $order->id;
                    $order_transaction->store_id = $store->id;
                    $order_transaction->user_id = $user->id;
                    $order_transaction->customer_id = $customer->id;
                    $order_transaction->type = 'refund';
                    $order_transaction->stripe_id = $refund->id;
                    $order_transaction->card_id = $card ? $card->id : null;
                    $order_transaction->amount = $difference;
                    $order_transaction->applyToBalance = $applyToBalance;
                    $order_transaction->save();

                    $difference += $charge->amount;
                }
            }
        }

        if ($applyToBalance) {
            $order->balance += $request->get('refundAmount');
        }
        $order->refundedAmount += $request->get('refundAmount');
        $order->coolerReturned = isset($cooler) && $cooler == 1 ? 1 : 0;
        $order->save();

        return 'Refunded $' . $request->get('refundAmount');
    }

    public function settleBalance(Request $request)
    {
        $order = Order::where('id', $request->get('orderId'))->first();
        $order->balance = 0;
        $order->save();
    }

    public function voidOrder(Request $request)
    {
        $order = Order::where('id', $request->get('orderId'))->first();
        if ($order->voided === 0) {
            $order->voided = 1;
            $order->save();
            return 'Order voided.';
        } else {
            $order->voided = 0;
            $order->save();
            return 'Order unvoided.';
        }
    }

    public function updateBalance(Request $request)
    {
        $order = Order::where('id', $request->get('id'))->first();
        $order->balance = $request->get('balance');
        $order->save();
    }

    public function emailCustomerReceipt(Request $request)
    {
        // Send email to customer
        $order = Order::where('id', $request->get('id'))->first();
        $customerUser = User::where('id', $order->user_id)->first();
        try {
            $customerUser->sendNotification('new_order', [
                'order' => $order ?? null,
                'pickup' => $order->pickup ?? null,
                'receipt' => true
            ]);
        } catch (\Exception $e) {
        }
    }

    public function getLineItemOrders($order_id)
    {
        return LineItemOrder::where('order_id', $order_id)
            ->get()
            ->map(function ($lineItemOrder) {
                return [
                    'price' => $lineItemOrder->price,
                    'quantity' => $lineItemOrder->quantity,
                    'title' => $lineItemOrder->title,
                    'full_title' => $lineItemOrder->full_title,
                    'size' => $lineItemOrder->size,
                    'production_group_id' => $lineItemOrder->production_group_id
                ];
            });
    }

    public function coolerReturned(Request $request)
    {
        $order = Order::where('id', $request->get('id'))->first();
        $order->coolerReturned = 1;
        $order->update();
    }
}
