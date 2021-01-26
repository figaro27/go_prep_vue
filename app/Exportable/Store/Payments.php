<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use Carbon\Carbon;
use App\ReportRecord;
use Illuminate\Support\Arr;

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
        $dailySummary =
            $this->params->get('dailySummary') === "true" ? true : false;
        $byOrderDate =
            $this->params->get('byOrderDate') === "true" ? true : false;
        $removeManualOrders =
            $this->params->get('removeManualOrders') === "true" ? true : false;
        $removeCashOrders =
            $this->params->get('removeCashOrders') === "true" ? true : false;
        $params->date_format = $this->store->settings->date_format;
        $currency = $this->store->settings->currency;
        $params->currency = $currency;

        $columns = [
            'created_at' => null,
            'delivery_date' => null,
            'preFeePreDiscount' => 0,
            'couponReduction' => 0,
            'mealPlanDiscount' => 0,
            'salesTax' => 0,
            'processingFee' => 0,
            'deliveryFee' => 0,
            'gratuity' => 0,
            'coolerDeposit' => 0,
            'purchasedGiftCardReduction' => 0,
            'referralReduction' => 0,
            'promotionReduction' => 0,
            'pointsReduction' => 0,
            'amount' => 0,
            'refundedAmount' => 0,
            'balance' => 0
        ];

        $columnSums = $columns;

        // Get regular payment rows
        $orders = $this->store
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
            ->where('voided', 0);

        $payments = $orders
            ->map(function ($order) use ($columns) {
                $columns['created_at'] = !$order->isMultipleDelivery
                    ? $order->created_at->format('D, m/d/Y')
                    : 'Multiple';
                $columns['delivery_date'] = !$order->isMultipleDelivery
                    ? $order->delivery_date->format('D, m/d/Y')
                    : 'Multiple';
                $columns['preFeePreDiscount'] = $order->preFeePreDiscount;
                $columns['couponReduction'] = $order->couponReduction;
                $columns['mealPlanDiscount'] = $order->mealPlanDiscount;
                $columns['salesTax'] = $order->salesTax;
                $columns['processingFee'] = $order->processingFee;
                $columns['deliveryFee'] = $order->deliveryFee;
                $columns['gratuity'] = $order->gratuity;
                $columns['coolerDeposit'] = $order->coolerDeposit;
                $columns['purchasedGiftCardReduction'] =
                    $order->purchasedGiftCardReduction;
                $columns['referralReduction'] = $order->referralReduction;
                $columns['promotionReduction'] = $order->promotionReduction;
                $columns['pointsReduction'] = $order->pointsReduction;
                $columns['amount'] = $order->amount;
                $columns['refundedAmount'] = $order->refundedAmount
                    ? $order->refundedAmount
                    : 0;
                $columns['balance'] = $order->balance ? $order->balance : 0;

                return $columns;
            })
            ->toArray();

        // Add all payment values together to get the sums row
        foreach ($payments as $payment) {
            foreach ($payment as $i => $p) {
                if (is_numeric($p)) {
                    $columnSums[$i] += $payment[$i];
                }
            }
        }

        // If the column sum totals 0, remove the column sum entirely and set the param for the blade report
        foreach ($columnSums as $i => $columnSum) {
            if ($byOrderDate) {
                $params['delivery_date'] = false;
                unset($columnSums['delivery_date']);
            } else {
                $params['created_at'] = false;
                unset($columnSums['created_at']);
            }
            if ($columnSum === 0.0 || $columnSum === 0) {
                $params[$i] = false;
                unset($columnSums[$i]);
            } else {
                $params[$i] = true;
            }
        }

        // If the column sum totals 0, remove the entire column of data
        $rows = [];

        foreach ($payments as $payment) {
            foreach ($payment as $i => $p) {
                if (!array_key_exists($i, $columnSums)) {
                    unset($payment[$i]);
                }
            }
            array_push($rows, $payment);
        }

        // Add the sums row to the beginning of the regular payment rows
        array_unshift($rows, $columnSums);

        // Daily summary

        $dayType = $byOrderDate ? 'order_day' : 'delivery_day';
        $groupedPayments = $orders->groupBy($dayType)->toArray();

        $dsRows = [];
        $sums = [];

        // Adding in total orders column for daily summary report
        $offset = 2;
        $columns =
            array_slice($columns, 0, $offset, true) + array('orders' => 0) +
            array_slice($columns, $offset, null, true);

        foreach ($groupedPayments as $i => $groupedPayment) {
            $sums = [$i => $columns];
            foreach ($groupedPayment as $payment) {
                $sums[$i]['created_at'] = $byOrderDate
                    ? Carbon::parse($payment['created_at'])->format('D, M d, Y')
                    : null;
                $sums[$i]['delivery_date'] = !$byOrderDate
                    ? Carbon::parse($payment['delivery_date'])->format(
                        'D, M d, Y'
                    )
                    : null;
                $sums[$i]['orders'] = count($groupedPayment);
                foreach ($payment as $x => $p) {
                    if (array_key_exists($x, $columnSums)) {
                        if (is_numeric($sums[$i][$x])) {
                            $sums[$i][$x] += $payment[$x];
                        }
                    }
                }
            }
            $sums = Arr::collapse($sums);
            $dsRows[] = $sums;
        }

        // If the column sum totals 0, remove the entire column of data
        $dailySummaryRows = [];

        foreach ($dsRows as $payment) {
            foreach ($payment as $i => $p) {
                if (!array_key_exists($i, $columnSums) && $i !== 'orders') {
                    unset($payment[$i]);
                }
            }
            array_push($dailySummaryRows, $payment);
        }

        $headers = [
            'created_at' => 'Order Date',
            'delivery_date' => 'Delivery Date',
            'orders' => 'Orders',
            'preFeePreDiscount' => 'Subtotal',
            'couponReduction' => '(Coupon)',
            'mealPlanDiscount' => '(Subscription)',
            'salesTax' => 'Sales Tax',
            'processingFee' => 'Processing Fee',
            'deliveryFee' => 'Delivery Fee',
            'gratuity' => 'Gratuity',
            'coolerDeposit' => 'Cooler Deposit',
            'purchasedGiftCardReduction' => '(Gift Card)',
            'referralReduction' => '(Referral)',
            'promotionReduction' => '(Promotion)',
            'pointsReduction' => '(Points)',
            'amount' => 'Total',
            'refundedAmount' => '(Refunded)',
            'balance' => 'Balance'
        ];

        // Append column headers to Excel report
        $columnHeaders = [];
        if ($type !== 'pdf') {
            foreach ($headers as $i => $header) {
                if (array_key_exists($i, $columnSums) || $i == 'orders') {
                    $columnHeaders[] = $headers[$i];
                }
            }
            array_unshift($rows, $columnHeaders);
            array_unshift($dailySummaryRows, $columnHeaders);
        }

        $reportRecord = ReportRecord::where(
            'store_id',
            $this->store->id
        )->first();
        $reportRecord->payments += 1;
        $reportRecord->update();

        if (!$dailySummary) {
            $params['dailySummary'] = false;
            return $rows;
        } else {
            return $dailySummaryRows;
        }
    }

    public function exportPdfView()
    {
        return 'reports.payments_pdf';
    }
}
