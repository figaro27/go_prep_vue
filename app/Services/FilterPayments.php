<?php

namespace App\Services;

use App\Store;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class FilterPayments
{
    /**
     * @return Illuminate\Database\Eloquent\Collection
     */
    protected $store;

    public function getPayments(Request $request)
    {
        $filters = $request->get('filters')
            ? $request->get('filters')
            : $request;

        $this->store = Store::where('id', $filters['storeId'])->first();

        if (is_string($filters['delivery_dates'])) {
            $dates = json_decode($filters['delivery_dates']);
            $startDate = $dates->from ? Carbon::parse($dates->from) : null;
            $endDate = $dates->to ? Carbon::parse($dates->to) : null;
        } else {
            $startDate = $filters['delivery_dates']['from']
                ? Carbon::parse($filters['delivery_dates']['from'])
                : null;
            $endDate = $filters['delivery_dates']['to']
                ? Carbon::parse($filters['delivery_dates']['to'])
                : null;
        }

        $byPaymentDate = $filters['byPaymentDate'];
        $removeCashOrders = $filters['removeCashOrders'];
        $removeManualOrders = $filters['removeManualOrders'];
        $couponId = $filters['couponId'];

        if ($startDate) {
            $startDate = Carbon::parse($startDate);
        } else {
            $startDate = $byPaymentDate
                ? Carbon::today($this->store->settings->timezone)
                    ->startOfDay()
                    ->subDays(7)
                : Carbon::today($this->store->settings->timezone)->startOfDay();
        }

        if ($endDate) {
            $endDate = Carbon::parse($endDate);
        } else {
            $endDate = $byPaymentDate
                ? Carbon::today($this->store->settings->timezone)->addDays(1)
                : Carbon::today($this->store->settings->timezone)
                    ->startOfDay()
                    ->addDays(7);
        }

        $application_fee = $this->store->settings->application_fee;

        if ($this->store->has('orders')) {
            $orders = $this->store->orders()->where(['paid' => 1]);
            $orders = $orders->where(function ($query) use (
                $startDate,
                $endDate,
                $byPaymentDate,
                $removeCashOrders,
                $removeManualOrders,
                $couponId
            ) {
                $query
                    ->where(function ($query1) use (
                        $startDate,
                        $endDate,
                        $byPaymentDate
                    ) {
                        $query1->where('isMultipleDelivery', 0);

                        if ($byPaymentDate) {
                            $query1->where(
                                'created_at',
                                '>=',
                                $startDate->format('Y-m-d')
                            );
                            $query1->where(
                                'created_at',
                                '<=',
                                $endDate->format('Y-m-d')
                            );
                        } else {
                            $query1->where(
                                'delivery_date',
                                '>=',
                                $startDate->format('Y-m-d')
                            );
                            $query1->where(
                                'delivery_date',
                                '<=',
                                $endDate->format('Y-m-d')
                            );
                        }
                    })
                    ->orWhere(function ($query2) use (
                        $startDate,
                        $endDate,
                        $byPaymentDate
                    ) {
                        $query2
                            ->where('isMultipleDelivery', 1)
                            ->whereHas('meal_orders', function ($subquery) use (
                                $startDate,
                                $endDate,
                                $byPaymentDate
                            ) {
                                $subquery->whereNotNull(
                                    'meal_orders.created_at'
                                );
                                if ($byPaymentDate) {
                                    $subquery->where(
                                        'meal_orders.created_at',
                                        '>=',
                                        $startDate->format('Y-m-d')
                                    );
                                    $subquery->where(
                                        'meal_orders.created_at',
                                        '<=',
                                        $endDate->format('Y-m-d')
                                    );
                                } else {
                                    $subquery->where(
                                        'meal_orders.delivery_date',
                                        '>=',
                                        $startDate->format('Y-m-d')
                                    );
                                    $subquery->where(
                                        'meal_orders.delivery_date',
                                        '<=',
                                        $endDate->format('Y-m-d')
                                    );
                                }
                            });
                    });
            });

            $orders = $removeCashOrders
                ? $orders->where('cashOrder', 0)
                : $orders;
            $orders = $removeManualOrders
                ? $orders->where('manual', 0)
                : $orders;

            $orders = $couponId
                ? $orders->where('coupon_id', $couponId)
                : $orders;

            $orders = $orders->get();

            foreach ($orders as $order) {
                // Adds any additinal charges on the order to the total order amount
                // Calculates and subtracts the total transaction fee (Stripe & GoPrep)
                $order->preTransactionFeeAmount =
                    $order->amount + $order->chargedAmount;
                $order->transactionFee =
                    $this->store->settings->payment_gateway === 'stripe'
                        ? ($order->afterDiscountBeforeFees +
                                $order->chargedAmount) *
                            ($application_fee / 100)
                        : 0;
                if (
                    !$order->cashOrder &&
                    $this->store->settings->payment_gateway === 'stripe'
                ) {
                    $order->transactionFee +=
                        ($order->amount + $order->chargedAmount) * 0.029 + 0.3;
                }

                $order->amount =
                    $order->amount +
                    $order->chargedAmount -
                    $order->transactionFee;
            }

            $orders->makeHidden([
                'has_notes',
                'meal_ids',
                'items',
                'meal_orders',
                'meal_package_items',
                'store_name',
                'cutoff_date',
                'cutoff_passed',
                'pre_coupon',
                'delivery_day',
                'goprep_fee',
                'stripe_fee',
                'grandTotal',
                'line_items_order',
                'added_by_store_id',
                'multiple_dates',
                'delivery_dates_array',
                'purchased_gift_card_code',
                'customer_name',
                'customer_address',
                'customer_zip',
                'visible_items',
                'pickup_location_name',
                'staff_member',
                'transfer_type'
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
}
