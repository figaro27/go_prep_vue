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
        $byOrderDate = $this->params->get('byOrderDate');
        $removeManualOrders = $this->params->get('removeManualOrders');
        $removeCashOrders = $this->params->get('removeCashOrders');

        $params->date_format = $this->store->settings->date_format;

        $sums = ['TOTALS', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $sumsByDaily = ['TOTALS', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        if ($dailySummary != 1) {
            $payments = $this->store
                ->getOrders(
                    null,
                    $this->getDeliveryDates(),
                    null,
                    true,
                    null,
                    $byOrderDate ? true : false,
                    $couponCode,
                    $removeManualOrders ? true : false,
                    $removeCashOrders ? true : false
                )
                ->where('voided', 0)
                ->map(function ($payment) use (&$sums, $byOrderDate) {
                    $sums[2] += $payment->preFeePreDiscount;
                    $sums[3] += $payment->couponReduction;
                    $sums[4] += $payment->mealPlanDiscount;
                    $sums[5] += $payment->salesTax;
                    $sums[6] += $payment->processingFee;
                    $sums[7] += $payment->deliveryFee;
                    $sums[8] += $payment->purchasedGiftCardReduction;
                    $sums[9] += $payment->referralReduction;
                    $sums[10] += $payment->promotionReduction;
                    $sums[11] += $payment->pointsReduction;
                    $sums[12] += $payment->amount;
                    $sums[13] += $payment->refundedAmount;
                    $sums[14] += $payment->balance;

                    $paymentsRows = [
                        $payment->created_at->format('D, m/d/Y'),
                        !$payment->isMultipleDelivery
                            ? $payment->delivery_date->format('D, m/d/Y')
                            : 'Multiple',
                        '$' . number_format($payment->preFeePreDiscount, 2),
                        '$' . number_format($payment->couponReduction, 2),
                        '$' . number_format($payment->mealPlanDiscount, 2),
                        '$' . number_format($payment->salesTax, 2),
                        '$' . number_format($payment->processingFee, 2),
                        '$' . number_format($payment->deliveryFee, 2),
                        '$' .
                            number_format(
                                $payment->purchasedGiftCardReduction,
                                2
                            ),
                        '$' . number_format($payment->referralReduction, 2),
                        '$' . number_format($payment->promotionReduction, 2),
                        '$' . number_format($payment->pointsReduction, 2),
                        '$' . number_format($payment->amount, 2),
                        '$' . number_format($payment->refundedAmount, 2),
                        '$' . number_format($payment->balance, 2)
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
                ->where('voided', 0)
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
                $salesTax = 0;
                $processingFee = 0;
                $deliveryFee = 0;
                $purchasedGiftCardReduction = 0;
                $referralReduction = 0;
                $promotionReduction = 0;
                $pointsReduction = 0;
                $amount = 0;
                $refundedAmount = 0;
                $balance = 0;

                foreach ($orderByDay as $order) {
                    $created_at = $order->order_day;
                    $delivery_date = $order->delivery_date;
                    $totalOrders += 1;
                    $preFeePreDiscount += $order->preFeePreDiscount;
                    $couponReduction += $order->couponReduction;
                    $mealPlanDiscount += $order->mealPlanDiscount;
                    $salesTax += $order->salesTax;
                    $processingFee += $order->processingFee;
                    $deliveryFee += $order->deliveryFee;
                    // $goPrepFeeAmount += $order->goprep_fee;
                    // $stripeFeeAmount += $order->stripe_fee;
                    $purchasedGiftCardReduction +=
                        $order->purchasedGiftCardReduction;
                    $referralReduction += $order->referralReduction;
                    $promotionReduction += $order->promotionReduction;
                    $pointsReduction += $order->pointsReduction;
                    $amount += $order->amount;
                    $refundedAmount += $order->refundedAmount;
                    $balance += $order->balance;
                    // $refundedAmount += $order->refundedAmount;
                }
                $orderDay = Carbon::createFromFormat(
                    'm d',
                    $created_at
                )->format('D, M d, Y');

                $deliveryDay = !$order->isMultipleDelivery
                    ? Carbon::createFromFormat('m d', $delivery_date)->format(
                        'D, M d, Y'
                    )
                    : 'Multiple';
                array_push($dailySums, [
                    $byOrderDate ? $orderDay : $deliveryDay,
                    $totalOrders,
                    '$' . number_format($preFeePreDiscount, 2),
                    '$' . number_format($couponReduction, 2),
                    '$' . number_format($mealPlanDiscount, 2),
                    '$' . number_format($salesTax, 2),
                    '$' . number_format($processingFee, 2),
                    '$' . number_format($deliveryFee, 2),
                    '$' . number_format($purchasedGiftCardReduction, 2),
                    '$' . number_format($referralReduction, 2),
                    '$' . number_format($promotionReduction, 2),
                    '$' . number_format($pointsReduction, 2),
                    '$' . number_format($amount, 2),
                    '$' . number_format($refundedAmount, 2),
                    '$' . number_format($balance, 2)
                ]);

                $sumsByDaily[1] += $totalOrders;
                $sumsByDaily[2] += $preFeePreDiscount;
                $sumsByDaily[3] += $couponReduction;
                $sumsByDaily[4] += $mealPlanDiscount;
                $sumsByDaily[5] += $salesTax;
                $sumsByDaily[6] += $processingFee;
                $sumsByDaily[7] += $deliveryFee;
                $sumsByDaily[8] += $purchasedGiftCardReduction;
                $sumsByDaily[9] += $referralReduction;
                $sumsByDaily[10] += $promotionReduction;
                $sumsByDaily[11] += $pointsReduction;
                $sumsByDaily[12] += $amount;
                $sumsByDaily[13] += $refundedAmount;
                $sumsByDaily[14] += $balance;
            }

            foreach ([2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13] as $i) {
                $sumsByDaily[$i] = '$' . number_format($sumsByDaily[$i], 2);
            }

            array_unshift($dailySums, $sumsByDaily);
            return $dailySums;
        }

        // Format the sum row
        foreach ([2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13] as $i) {
            $sums[$i] = '$' . number_format($sums[$i], 2);
        }

        // Push the sums to the start of the list
        $payments->prepend($sums);

        // Remove unused columns from report
        $removedIndexes = [];
        $filteredPayments = [];

        $params['removedIndex'] = $removedIndexes;
        foreach ($payments[0] as $i => $payment) {
            if ($payment === 0 || $payment === '$0.00') {
                array_push($removedIndexes, $i);
            }
        }

        foreach ($payments as $payment) {
            foreach ($removedIndexes as $removedIndex) {
                unset($payment[$removedIndex]);
            }
            array_push($filteredPayments, $payment);
        }

        // Remove unused columns from blade template
        $params['removeCoupon'] = in_array(3, $removedIndexes) ? true : false;
        $params['removeSubscription'] = in_array(4, $removedIndexes)
            ? true
            : false;
        $params['removeSalesTax'] = in_array(5, $removedIndexes) ? true : false;
        $params['removeProcessingFee'] = in_array(6, $removedIndexes)
            ? true
            : false;
        $params['removeDeliveryFee'] = in_array(7, $removedIndexes)
            ? true
            : false;
        $params['removeGiftCard'] = in_array(8, $removedIndexes) ? true : false;
        $params['removeReferral'] = in_array(9, $removedIndexes) ? true : false;
        $params['removePromotion'] = in_array(10, $removedIndexes)
            ? true
            : false;
        $params['removePoints'] = in_array(11, $removedIndexes) ? true : false;
        $params['removeRefund'] = in_array(13, $removedIndexes) ? true : false;
        $params['removeBalance'] = in_array(14, $removedIndexes) ? true : false;

        $filteredHeaders[0] = ['Order Date', 'Delivery Date', 'Subtotal'];

        // Probably a better way to do this.

        if (!in_array(3, $removedIndexes)) {
            array_push($filteredHeaders[0], 'Coupon');
        }
        if (!in_array(4, $removedIndexes)) {
            array_push($filteredHeaders[0], 'Subscription');
        }
        if (!in_array(5, $removedIndexes)) {
            array_push($filteredHeaders[0], 'Sales Tax');
        }
        if (!in_array(6, $removedIndexes)) {
            array_push($filteredHeaders[0], 'Processing Fee');
        }
        if (!in_array(7, $removedIndexes)) {
            array_push($filteredHeaders[0], 'Delivery Fee');
        }
        if (!in_array(8, $removedIndexes)) {
            array_push($filteredHeaders[0], 'Gift Card');
        }
        if (!in_array(9, $removedIndexes)) {
            array_push($filteredHeaders[0], 'Referral');
        }
        if (!in_array(10, $removedIndexes)) {
            array_push($filteredHeaders[0], 'Promotion');
        }
        if (!in_array(11, $removedIndexes)) {
            array_push($filteredHeaders[0], 'Points');
        }
        array_push($filteredHeaders[0], 'Total');
        if (!in_array(13, $removedIndexes)) {
            array_push($filteredHeaders[0], 'Refund');
        }
        if (!in_array(14, $removedIndexes)) {
            array_push($filteredHeaders[0], 'Balance');
        }

        if ($type !== 'pdf') {
            $filteredPayments = array_merge(
                $filteredHeaders,
                $filteredPayments
            );
        }

        return $filteredPayments;
    }

    public function exportPdfView()
    {
        return 'reports.payments_pdf';
    }
}
