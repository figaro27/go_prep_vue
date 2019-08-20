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

        $sums = ['TOTALS', 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0];

        if ($dailySummary != 1) {
            $payments = $this->store
                ->getOrders(
                    null,
                    $this->getDeliveryDates(),
                    null,
                    null,
                    null,
                    true,
                    $couponCode
                )
                ->map(function ($payment) use (&$sums) {
                    $goPrepFee = $this->store->settings->application_fee / 100;
                    $stripeFee = 0.029;

                    $sums[1] += $payment->preFeePreDiscount;
                    $sums[3] += $payment->couponReduction;
                    $sums[4] += $payment->mealPlanDiscount;
                    $sums[5] += $payment->processingFee;
                    $sums[6] += $payment->deliveryFee;
                    $sums[7] += $payment->salesTax;
                    $sums[8] += $payment->afterDiscountBeforeFees * $goPrepFee;
                    $sums[9] += $payment->amount * $stripeFee + 0.3;
                    $sums[10] +=
                        $payment->amount -
                        $payment->afterDiscountBeforeFees * $goPrepFee -
                        $payment->amount * $stripeFee -
                        0.3;
                    $sums[11] = 100 - $payment->deposit;

                    $paymentsRows = [
                        $payment->created_at->format('D, m/d/Y'),
                        '$' . number_format($payment->preFeePreDiscount, 2),
                        $payment->couponCode,
                        '$' . number_format($payment->couponReduction, 2),
                        '$' . number_format($payment->mealPlanDiscount, 2),
                        '$' . number_format($payment->deliveryFee, 2),
                        '$' . number_format($payment->processingFee, 2),
                        '$' . number_format($payment->salesTax, 2),
                        '$' .
                            number_format(
                                $payment->afterDiscountBeforeFees * $goPrepFee,
                                2
                            ),
                        '$' .
                            number_format(
                                $payment->amount * $stripeFee + 0.3,
                                2
                            ),
                        '$' .
                            number_format(
                                $payment->amount -
                                    $payment->afterDiscountBeforeFees *
                                        $goPrepFee -
                                    $payment->amount * $stripeFee -
                                    0.3,
                                2
                            ),
                        100 - $payment->deposit . '%'
                    ];

                    return $paymentsRows;
                });
        } else {
            $ordersByDay = $this->store
                ->getOrders(
                    null,
                    $this->getDeliveryDates(),
                    null,
                    null,
                    null,
                    true,
                    $couponCode
                )
                ->groupBy('order_day');

            $dailySums = [];

            foreach ($ordersByDay as $orderByDay) {
                $goPrepFee = $this->store->settings->application_fee / 100;
                $stripeFee = 0.029;

                $created_at = "";
                $totalOrders = 0;
                $preFeePreDiscount = 0;
                $mealPlanDiscount = 0;
                $couponReduction = 0;
                $afterDiscountBeforeFees = 0;
                $processingFee = 0;
                $deliveryFee = 0;
                $salesTax = 0;
                $goPrepFeeAmount = 0;
                $stripeFeeAmount = 0;
                $amount = 0;

                foreach ($orderByDay as $order) {
                    $created_at = $order->order_day;
                    $totalOrders += 1;
                    $preFeePreDiscount += $order->preFeePreDiscount;
                    $couponReduction += $order->couponReduction;
                    $mealPlanDiscount += $order->mealPlanDiscount;
                    $processingFee += $order->processingFee;
                    $deliveryFee += $order->deliveryFee;
                    $salesTax += $order->salesTax;
                    $goPrepFeeAmount +=
                        $order->afterDiscountBeforeFees * $goPrepFee;
                    $stripeFeeAmount +=
                        ($order->afterDiscountBeforeFees +
                            $order->deliveryFee +
                            $order->processingFee +
                            $order->salesTax) *
                            $stripeFee +
                        0.3;
                    $amount +=
                        $order->amount -
                        $goPrepFee * $order->amount -
                        $stripeFee * $order->amount -
                        0.3;
                    // $deposit = 100 - $order->deposit;
                }
                $orderDay = Carbon::createFromFormat(
                    'm d',
                    $created_at
                )->format('D, M d, Y');
                array_push($dailySums, [
                    $orderDay,
                    $totalOrders,
                    '$' . number_format($preFeePreDiscount, 2),
                    '$' . number_format($couponReduction, 2),
                    '$' . number_format($mealPlanDiscount, 2),
                    '$' . number_format($deliveryFee, 2),
                    '$' . number_format($processingFee, 2),
                    '$' . number_format($salesTax, 2),
                    '$' . number_format($goPrepFeeAmount, 2),
                    '$' . number_format($stripeFeeAmount, 2),
                    '$' . number_format($amount, 2)
                ]);
            }

            return $dailySums;
        }

        // Format the sum row
        foreach ([1, 3, 4, 5, 6, 7, 8, 9, 10, 11] as $i) {
            $sums[$i] = '$' . number_format($sums[$i], 2);
        }

        // Push the sums to the start of the list
        $payments->prepend($sums);

        if ($type !== 'pdf') {
            $payments->prepend([
                'Payment Date',
                'Subtotal',
                'Coupon',
                'Coupon Reduction',
                'Meal Plan Discount',
                'Processing Fee',
                'Delivery Fee',
                'Sales Tax',
                'GoPrep Fee',
                'Stripe Fee',
                'Total',
                'Balance'
            ]);
        }

        return $payments->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.payments_pdf';
    }
}
