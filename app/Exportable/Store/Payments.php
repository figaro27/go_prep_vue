<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;

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

        $sums = ['TOTALS', 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];

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

                // manipulate $sums here
                $sums[1]++;

                $paymentsRows = [
                    $payment->created_at->format('D, m/d/Y'),
                    '$' . number_format($payment->preFeePreDiscount, 2),
                    $payment->couponCode,
                    '$' . number_format($payment->couponReduction, 2),
                    '$' . number_format($payment->mealPlanDiscount, 2),
                    '$' . number_format($payment->processingFee, 2),
                    '$' . number_format($payment->deliveryFee, 2),
                    '$' . number_format($payment->salesTax, 2),
                    '$' . number_format($payment->amount, 2),
                    '$' .
                        number_format(
                            $payment->afterDiscountBeforeFees * $goPrepFee,
                            2
                        ),
                    '$' . number_format($payment->amount * $stripeFee + 0.3, 2),
                    '$' .
                        number_format(
                            $payment->amount -
                                $payment->afterDiscountBeforeFees * $goPrepFee -
                                $payment->amount * $stripeFee -
                                0.3,
                            2
                        ),
                    100 - $payment->deposit . '%'
                ];

                return $paymentsRows;
            });

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
                'PreFee Total',
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
