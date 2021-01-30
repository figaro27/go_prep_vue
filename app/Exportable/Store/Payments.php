<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use Carbon\Carbon;
use App\ReportRecord;
use Illuminate\Support\Arr;
use App\Services\FilterPayments;
use Illuminate\Http\Request;

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

        $dailySummary =
            $this->params->get('dailySummary') == "true" ? true : false;
        $byPaymentDate =
            $this->params->get('byPaymentDate') == "true" ? true : false;
        $removeManualOrders =
            $this->params->get('removeManualOrders') == "true" ? true : false;
        $removeCashOrders =
            $this->params->get('removeCashOrders') == "true" ? true : false;

        $this->params->put('dailySummary', $dailySummary);
        $this->params->put('byPaymentDate', $byPaymentDate);
        $this->params->put('removeManualOrders', $removeManualOrders);
        $this->params->put('removeCashOrders', $removeCashOrders);

        $couponCode = $this->params->get('couponCode');

        $params->date_format = $this->store->settings->date_format;
        $currency = $this->store->settings->currency;
        $params->currency = $currency;
        $this->params->put('storeId', $this->store->id);

        $columns = [
            'paid_at' => null,
            'delivery_date' => null,
            'preFeePreDiscount' => 0,
            'couponReduction' => 0,
            'mealPlanDiscount' => 0,
            'salesTax' => 0,
            'processingFee' => 0,
            'deliveryFee' => 0,
            'purchasedGiftCardReduction' => 0,
            'gratuity' => 0,
            'coolerDeposit' => 0,
            'referralReduction' => 0,
            'promotionReduction' => 0,
            'pointsReduction' => 0,
            'chargedAmount' => 0,
            'preTransactionFeeAmount' => 0,
            'transactionFee' => 0,
            'amount' => 0,
            'refundedAmount' => 0,
            'balance' => 0
        ];

        $columnSums = $columns;

        $filterPayments = new FilterPayments();

        $request = new Request($params->toArray());

        $orders = $filterPayments->getPayments($request);

        $payments = $orders
            ->map(function ($order) use ($columns) {
                $columns['paid_at'] = !$order->isMultipleDelivery
                    ? Carbon::parse($order->paid_at)->format('D, m/d/Y')
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
                $columns['purchasedGiftCardReduction'] =
                    $order->purchasedGiftCardReduction;
                $columns['gratuity'] = $order->gratuity;
                $columns['coolerDeposit'] = $order->coolerDeposit;
                $columns['referralReduction'] = $order->referralReduction;
                $columns['promotionReduction'] = $order->promotionReduction;
                $columns['pointsReduction'] = $order->pointsReduction;
                $columns['chargedAmount'] = $order->chargedAmount;
                $columns['preTransactionFeeAmount'] = isset(
                    $order->preTransactionFeeAmount
                )
                    ? $order->preTransactionFeeAmount
                    : 0;
                $columns['transactionFee'] = isset($order->transactionFee)
                    ? $order->transactionFee
                    : 0;
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
            $columnSums['paid_at'] = 'TOTALS';
            $columnSums['delivery_date'] = 'TOTALS';
            if ($byPaymentDate) {
                $params['delivery_date'] = false;
                unset($columnSums['delivery_date']);
            } else {
                $params['paid_at'] = false;
                unset($columnSums['paid_at']);
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

        $dayType = $byPaymentDate ? 'order_day' : 'delivery_day';
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
                $sums[$i]['paid_at'] = $byPaymentDate
                    ? Carbon::parse($payment['paid_at'])->format('D, M d, Y')
                    : null;
                $sums[$i]['delivery_date'] = !$byPaymentDate
                    ? Carbon::parse($payment['delivery_date'])->format(
                        'D, M d, Y'
                    )
                    : null;
                $sums[$i]['orders'] = count($groupedPayment);
                foreach ($payment as $x => $p) {
                    if ($x === 'paid_at' || $x === 'delivery_date') {
                        $payment[$x] = 'TOTALS';
                    }
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
            'paid_at' => 'Payment Date',
            'delivery_date' => 'Delivery Date',
            // 'orders' => 'Orders',
            'preFeePreDiscount' => 'Subtotal',
            'couponReduction' => '(Coupon)',
            'mealPlanDiscount' => '(Subscription)',
            'salesTax' => 'Sales Tax',
            'processingFee' => 'Processing Fee',
            'deliveryFee' => 'Delivery Fee',
            'purchasedGiftCardReduction' => '(Gift Card)',
            'gratuity' => 'Gratuity',
            'coolerDeposit' => 'Cooler Deposit',
            'referralReduction' => '(Referral)',
            'promotionReduction' => '(Promotion)',
            'pointsReduction' => '(Points)',
            'chargedAmount' => 'Additional Charges',
            'preTransactionFeeAmount' => 'Pre-Fee Total',
            'transactionFee' => '(Transaction Fee)',
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

                if ($dailySummary && $i === 'delivery_date') {
                    $columnHeaders[1] = 'Orders';
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

        // Format cells
        $formattedRows = [];
        foreach ($rows as $row) {
            $formattedRow = [];
            foreach ($row as $i => $cell) {
                if (is_numeric($cell) && $i !== 'orders') {
                    $formattedRow[$i] = number_format(
                        (float) $cell,
                        2,
                        '.',
                        ''
                    );
                } else {
                    $formattedRow[$i] = $cell;
                }
            }
            $formattedRows[] = $formattedRow;
        }

        $formattedDailySummaryRows = [];
        foreach ($dailySummaryRows as $row) {
            $formattedRow = [];
            foreach ($row as $i => $cell) {
                if (is_numeric($cell) && $i !== 'orders') {
                    $formattedRow[$i] = number_format(
                        (float) $cell,
                        2,
                        '.',
                        ''
                    );
                } else {
                    $formattedRow[$i] = $cell;
                }
            }
            $formattedDailySummaryRows[] = $formattedRow;
        }

        if (!$dailySummary) {
            $params['dailySummary'] = false;
            return $formattedRows;
        } else {
            return $formattedDailySummaryRows;
        }
    }

    public function exportPdfView()
    {
        return 'reports.payments_pdf';
    }
}
