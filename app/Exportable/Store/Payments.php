<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use Carbon\Carbon;

class Payments
{
    use Exportable;

    protected $store;

    public function __construct(Store $store, $params = [])
    {
        $this->store = $store;
        $this->params = $params;
    }

    public function exportData($type = null)
    {
        $params = $this->params;
        $couponCode = $this->params->get('couponCode');
        $dailySummary = $this->params->get('dailySummary');
        $byDeliveryDate = $this->params->get('byDeliveryDate');

        $sums = ['TOTALS', 0, '', 0, 0, 0, 0, 0, 0, 0];
        $sumsByDaily = ['TOTALS', 0, 0, 0, 0, 0, 0, 0, 0, 0];

        if ($dailySummary != 1) {
            $payments = $this->store
                ->getOrders(
                    null,
                    $this->getDeliveryDates(),
                    null,
                    true,
                    null,
                    $byDeliveryDate ? false : true,
                    $couponCode
                )
                ->map(function ($payment) use (&$sums, $byDeliveryDate) {
                    $sums[1] += $payment->preFeePreDiscount;
                    $sums[3] += $payment->couponReduction;
                    $sums[4] += $payment->mealPlanDiscount;
                    $sums[5] += $payment->deliveryFee;
                    $sums[6] += $payment->processingFee;
                    $sums[7] += $payment->salesTax;
                    // $sums[8] += $payment->goprep_fee;
                    // $sums[9] += $payment->stripe_fee;
                    $sums[8] += $payment->amount;
                    $sums[9] += $payment->balance;
                    // $sums[10] += $payment->refundedAmount;

                    $paymentsRows = [
                        $byDeliveryDate
                            ? $payment->delivery_date->format('D, m/d/Y')
                            : $payment->created_at->format('D, m/d/Y'),
                        '$' . number_format($payment->preFeePreDiscount, 2),
                        $payment->couponCode,
                        '$' . number_format($payment->couponReduction, 2),
                        '$' . number_format($payment->mealPlanDiscount, 2),
                        '$' . number_format($payment->deliveryFee, 2),
                        '$' . number_format($payment->processingFee, 2),
                        '$' . number_format($payment->salesTax, 2),
                        // '$' . number_format($payment->goprep_fee, 2),
                        // '$' . number_format($payment->stripe_fee, 2),
                        '$' . number_format($payment->amount, 2),
                        '$' . number_format($payment->balance, 2)
                        // '$' . number_format($payment->refundedAmount, 2)
                    ];

                    return $paymentsRows;
                });
        } else {
            $ordersByDay = $this->store
                ->getOrders(
                    null,
                    $this->getDeliveryDates(),
                    null,
                    true,
                    null,
                    true,
                    $couponCode
                )
                ->groupBy('order_day');

            $dailySums = [];

            foreach ($ordersByDay as $orderByDay) {
                $created_at = "";
                $delivery_date = "";
                $totalOrders = 0;
                $preFeePreDiscount = 0;
                $mealPlanDiscount = 0;
                $couponReduction = 0;
                $afterDiscountBeforeFees = 0;
                $processingFee = 0;
                $deliveryFee = 0;
                $salesTax = 0;
                // $goPrepFeeAmount = 0;
                // $stripeFeeAmount = 0;
                $amount = 0;
                $balance = 0;
                // $refundedAmount = 0;

                foreach ($orderByDay as $order) {
                    $created_at = $order->order_day;
                    $delivery_date = $order->delivery_date;
                    $totalOrders += 1;
                    $preFeePreDiscount += $order->preFeePreDiscount;
                    $couponReduction += $order->couponReduction;
                    $mealPlanDiscount += $order->mealPlanDiscount;
                    $processingFee += $order->processingFee;
                    $deliveryFee += $order->deliveryFee;
                    $salesTax += $order->salesTax;
                    // $goPrepFeeAmount += $order->goprep_fee;
                    // $stripeFeeAmount += $order->stripe_fee;
                    $amount += $order->amount;
                    $balance += $order->balance;
                    // $refundedAmount += $order->refundedAmount;
                }
                $orderDay = Carbon::createFromFormat(
                    'm d',
                    $created_at
                )->format('D, M d, Y');

                $deliveryDay = Carbon::createFromFormat(
                    'm d',
                    $delivery_date
                )->format('D, M d, Y');
                array_push($dailySums, [
                    $byDeliveryDate ? $deliveryDay : $orderDay,
                    $totalOrders,
                    '$' . number_format($preFeePreDiscount, 2),
                    '$' . number_format($couponReduction, 2),
                    '$' . number_format($mealPlanDiscount, 2),
                    '$' . number_format($deliveryFee, 2),
                    '$' . number_format($processingFee, 2),
                    '$' . number_format($salesTax, 2),
                    // '$' . number_format($goPrepFeeAmount, 2),
                    // '$' . number_format($stripeFeeAmount, 2),
                    '$' . number_format($amount, 2),
                    '$' . number_format($balance, 2)
                    // '$' . number_format($refundedAmount, 2)
                ]);

                $sumsByDaily[1] += $totalOrders;
                $sumsByDaily[2] += $preFeePreDiscount;
                $sumsByDaily[3] += $couponReduction;
                $sumsByDaily[4] += $mealPlanDiscount;
                $sumsByDaily[5] += $deliveryFee;
                $sumsByDaily[6] += $processingFee;
                $sumsByDaily[7] += $salesTax;
                // $sumsByDaily[8] += $goPrepFeeAmount;
                // $sumsByDaily[9] += $stripeFeeAmount;
                $sumsByDaily[8] += $amount;
                $sumsByDaily[9] += $balance;
                // $sumsByDaily[10] += $refundedAmount;
            }

            foreach ([2, 3, 4, 5, 6, 7, 8, 9] as $i) {
                $sumsByDaily[$i] = '$' . number_format($sumsByDaily[$i], 2);
            }

            array_unshift($dailySums, $sumsByDaily);
            return $dailySums;
        }

        // Format the sum row
        foreach ([1, 3, 4, 5, 6, 7, 8, 9] as $i) {
            $sums[$i] = '$' . number_format($sums[$i], 2);
        }

        // Push the sums to the start of the list
        $payments->prepend($sums);

        if ($type !== 'pdf') {
            $payments->prepend([
                'Date',
                'Subtotal',
                'Coupon',
                'Coupon Reduction',
                'Subscription Discount',
                'Processing Fee',
                'Delivery Fee',
                'Sales Tax',
                // 'GoPrep Fee',
                // 'Stripe Fee',
                'Total',
                'Balance'
                // 'Refunded'
            ]);
        }

        return $payments->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.payments_pdf';
    }
}
