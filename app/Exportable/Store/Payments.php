<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use App\ReportRecord;
use Illuminate\Support\Arr;
use App\Services\FilterPayments;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Payout;

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
        $this->params->put('store', $this->store->details->name);
        $this->params->put('report', 'Payments');
        $this->params->put('date', Carbon::now()->format('m-d-Y'));

        $params = $this->params;

        $dailySummary =
            $this->params->get('dailySummary') == "true" ? true : false;
        $byPaymentDate =
            $this->params->get('byPaymentDate') == "true" ? true : false;
        $removeManualOrders =
            $this->params->get('removeManualOrders') == "true" ? true : false;
        $removeCashOrders =
            $this->params->get('removeCashOrders') == "true" ? true : false;
        $includePayouts =
            $this->params->get('includePayouts') == "true" ? true : false;

        $payoutId = $this->params->get('payoutId');
        $payoutDate = Payout::where('id', $payoutId)
            ->pluck('arrival_date')
            ->first();
        $formattedPayoutDate = $payoutDate
            ? Carbon::parse($payoutDate)->format('D, m/d/Y')
            : null;

        $this->params->put('dailySummary', $dailySummary);
        $this->params->put('byPaymentDate', $byPaymentDate);
        $this->params->put('removeManualOrders', $removeManualOrders);
        $this->params->put('removeCashOrders', $removeCashOrders);
        $this->params->put('includePayouts', $includePayouts);
        $this->params->put('payoutDate', $payoutDate);
        $this->params->put('formattedPayoutDate', $formattedPayoutDate);

        $couponCode = $this->params->get('couponCode');

        $params->date_format = $this->store->settings->date_format;
        $currency = $this->store->settings->currency;
        $params->currency = $currency;
        $this->params->put('storeId', $this->store->id);

        $columns = [
            'payout_total' => 0,
            'payout_date' => null,
            'paid_at' => null,
            'delivery_date' => null,
            'order_number' => null,
            'orders' => 0,
            'customer_name' => null,
            'preFeePreDiscount' => 0,
            'couponReduction' => 0,
            'couponCode' => null,
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
            'preTransactionFeeAmount' => 0,
            'transactionFee' => 0,
            'originalAmount' => 0,
            'adjustedDifference' => 0,
            'chargedAmount' => 0,
            'refundedAmount' => 0,
            'balance' => 0
        ];

        if (!$dailySummary) {
            unset($columns['orders']);
        }

        $columnSums = $columns;

        $filterPayments = new FilterPayments();

        $request = new Request($params->toArray());

        $orders = $filterPayments->getPayments($request);

        $payments = $orders
            ->map(function ($order) use ($columns) {
                $columns['payout_total'] = $order->payout_total;
                $columns['payout_date'] = $order->payout_date
                    ? Carbon::parse($order->payout_date)->format('D, m/d/Y')
                    : 'Pending';
                $columns['paid_at'] = Carbon::parse($order->paid_at)->format(
                    'D, m/d/Y'
                );
                $columns['delivery_date'] = !$order->isMultipleDelivery
                    ? $order->delivery_date->format('D, m/d/Y')
                    : 'Multiple';
                $columns['order_number'] = $order->order_number ?? null;
                $columns['customer_name'] = $order->customer_name ?? null;
                $columns['preFeePreDiscount'] = $order->preFeePreDiscount;
                $columns['couponReduction'] = $order->couponReduction;
                $columns['couponCode'] = $order->couponCode;
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
                $columns['preTransactionFeeAmount'] = isset(
                    $order->preTransactionFeeAmount
                )
                    ? $order->preTransactionFeeAmount
                    : 0;
                $columns['transactionFee'] = isset($order->transactionFee)
                    ? $order->transactionFee
                    : 0;
                $columns['originalAmount'] = $order->originalAmount;
                $columns['adjustedDifference'] = $order->adjustedDifference;
                $columns['chargedAmount'] = $order->chargedAmount;
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
                if ($i !== 'payout_total' && $i !== 'payout_date') {
                    if (is_numeric($p)) {
                        $columnSums[$i] += $payment[$i];
                    }
                }
            }
        }
        // If the column sum totals 0, remove the column sum entirely and set the param for the blade report

        foreach ($columnSums as $i => $columnSum) {
            $columnSums['delivery_date'] = '';
            $columnSums['order_number'] = '';

            if (
                ($columnSum === 0.0 || $columnSum === 0) &&
                $i !== 'orders' &&
                $i !== 'payout_date' &&
                $i !== 'payout_total'
            ) {
                $params[$i] = false;
                unset($columnSums[$i]);
                if ($i === 'couponReduction') {
                    unset($columnSums['couponCode']);
                }
            } else {
                $params[$i] = true;
            }

            if ($i === 'payout_total') {
                $columnSums[$i] = 'TOTALS';
            }

            if (!$includePayouts) {
                $columnSums['paid_at'] = 'TOTALS';
                if ($i === 'payout_date' || $i === 'payout_total') {
                    unset($columnSums[$i]);
                }
            }
        }

        // If the column sum totals 0, remove the entire column of data
        $rows = [];

        foreach ($payments as $payment) {
            foreach ($payment as $i => $p) {
                if (
                    !array_key_exists($i, $columnSums) &&
                    $i !== 'payout_date' &&
                    $i !== 'payout_total'
                ) {
                    unset($payment[$i]);
                }

                if (!$includePayouts) {
                    if ($i === 'payout_date' || $i === 'payout_total') {
                        unset($payment[$i]);
                    }
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
        $offset = 4;
        $columns =
            array_slice($columns, 0, $offset, true) + array('orders' => 0) +
            array_slice($columns, $offset, null, true);

        foreach ($groupedPayments as $i => $groupedPayment) {
            $sums = [$i => $columns];
            foreach ($groupedPayment as $payment) {
                $sums[$i]['paid_at'] = Carbon::parse(
                    $payment['paid_at']
                )->format('D, M d, Y');
                $sums[$i]['delivery_date'] = Carbon::parse(
                    $payment['delivery_date']
                )->format('D, M d, Y');
                $sums[$i]['orders'] = count($groupedPayment);
                foreach ($payment as $x => $p) {
                    if ($x === 'paid_at') {
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
                if (!array_key_exists($i, $columnSums)) {
                    unset($payment[$i]);
                }
            }
            unset($payment['couponCode']);
            array_push($dailySummaryRows, $payment);
        }

        $headers = [];

        if ($includePayouts) {
            $headers += [
                'payout_total' => 'Payout Total',
                'payout_date' => 'Payout Date'
            ];
        }

        $headers += [
            'paid_at' => 'Payment Date',
            'delivery_date' => 'Delivery Date',
            'order_number' => 'Order',
            'customer_name' => 'Customer',
            'orders' => 'Orders',
            'preFeePreDiscount' => 'Subtotal',
            'couponReduction' => '(Coupon)',
            'couponCode' => '(Coupon Code)',
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
            'preTransactionFeeAmount' => 'Pre-Fee Total',
            'transactionFee' => '(Transaction Fee)',
            'originalAmount' => 'Total',
            'adjustedDifference' => 'Adjusted Difference',
            'chargedAmount' => 'Additional Charges',
            'refundedAmount' => '(Refunded)',
            'balance' => 'Balance'
        ];

        // Append column headers to Excel report
        $columnHeaders = [];

        if ($type !== 'pdf') {
            foreach ($headers as $i => $header) {
                if (array_key_exists($i, $columnSums)) {
                    if (
                        $dailySummary &&
                        $i !== 'order_number' &&
                        $i !== 'customer_name' &&
                        $i !== 'couponCode'
                    ) {
                        $columnHeaders[] = $headers[$i];
                    } elseif (!$dailySummary && $i !== 'orders') {
                        $columnHeaders[] = $headers[$i];
                    }
                }
            }
            array_unshift($rows, $columnHeaders);
            array_unshift($dailySummaryRows, $columnHeaders);
        } else {
            if (!$dailySummary) {
                unset($columnSums['orders']);
            }
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
                if (
                    is_numeric($cell) &&
                    $i !== 'orders' &&
                    $i !== 'order_number'
                ) {
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
                if ($i !== 'order_number' && $i !== 'customer_name') {
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
