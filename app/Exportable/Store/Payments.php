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
            'created_at' => null,
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
                $columns['created_at'] = !$order->isMultipleDelivery
                    ? $order->created_at->format('D, m/d/Y')
                    : 'Multiple';
                $columns['delivery_date'] = !$order->isMultipleDelivery
                    ? $order->delivery_date->format('D, m/d/Y')
                    : 'Multiple';
                $columns['preFeePreDiscount'] = number_format(
                    (float) $order->preFeePreDiscount,
                    2,
                    '.',
                    ''
                );
                $columns['couponReduction'] = number_format(
                    (float) $order->couponReduction,
                    2,
                    '.',
                    ''
                );
                $columns['mealPlanDiscount'] = number_format(
                    (float) $order->mealPlanDiscount,
                    2,
                    '.',
                    ''
                );
                $columns['salesTax'] = number_format(
                    (float) $order->salesTax,
                    2,
                    '.',
                    ''
                );
                $columns['processingFee'] = number_format(
                    (float) $order->processingFee,
                    2,
                    '.',
                    ''
                );
                $columns['deliveryFee'] = number_format(
                    (float) $order->deliveryFee,
                    2,
                    '.',
                    ''
                );
                $columns['purchasedGiftCardReduction'] = number_format(
                    (float) $order->purchasedGiftCardReduction,
                    2,
                    '.',
                    ''
                );
                $columns['gratuity'] = number_format(
                    (float) $order->gratuity,
                    2,
                    '.',
                    ''
                );
                $columns['coolerDeposit'] = number_format(
                    (float) $order->coolerDeposit,
                    2,
                    '.',
                    ''
                );
                $columns['referralReduction'] = number_format(
                    (float) $order->referralReduction,
                    2,
                    '.',
                    ''
                );
                $columns['promotionReduction'] = number_format(
                    (float) $order->promotionReduction,
                    2,
                    '.',
                    ''
                );
                $columns['pointsReduction'] = number_format(
                    (float) $order->pointsReduction,
                    2,
                    '.',
                    ''
                );
                $columns['chargedAmount'] = number_format(
                    (float) $order->chargedAmount,
                    2,
                    '.',
                    ''
                );
                $columns['preTransactionFeeAmount'] = isset(
                    $order->preTransactionFeeAmount
                )
                    ? number_format(
                        (float) $order->preTransactionFeeAmount,
                        2,
                        '.',
                        ''
                    )
                    : 0;
                $columns['transactionFee'] = isset($order->transactionFee)
                    ? number_format((float) $order->transactionFee, 2, '.', '')
                    : 0;
                $columns['amount'] = number_format(
                    (float) $order->amount,
                    2,
                    '.',
                    ''
                );
                $columns['refundedAmount'] = $order->refundedAmount
                    ? number_format((float) $$order->refundedAmount, 2, '.', '')
                    : 0;
                $columns['balance'] = $order->balance
                    ? number_format((float) $$order->balance, 2, '.', '')
                    : 0;

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
            $columnSums['created_at'] = 'TOTALS';
            $columnSums['delivery_date'] = 'TOTALS';
            if ($byPaymentDate) {
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
                $sums[$i]['created_at'] = $byPaymentDate
                    ? Carbon::parse($payment['created_at'])->format('D, M d, Y')
                    : null;
                $sums[$i]['delivery_date'] = !$byPaymentDate
                    ? Carbon::parse($payment['delivery_date'])->format(
                        'D, M d, Y'
                    )
                    : null;
                $sums[$i]['orders'] = count($groupedPayment);
                foreach ($payment as $x => $p) {
                    if ($x === 'created_at' || $x === 'delivery_date') {
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
            'created_at' => 'Order Date',
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
