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

        $payments = $this->store
            ->getOrders(null, $this->getDeliveryDates())
            ->map(function ($payment) {
                $goPrepFee = $this->store->settings->application_fee / 100;
                $stripeFee = 0.029;
                return [
                    $payment->created_at->format('D, m/d/Y'),
                    '$' . number_format($payment->preFeePreDiscount, 2),
                    '$' . number_format($payment->salesTax, 2),
                    '$' . number_format($payment->afterDiscountBeforeFees, 2),
                    '$' .
                        number_format(
                            $payment->afterDiscountBeforeFees * $goPrepFee,
                            2
                        ),
                    '$' . number_format($payment->amount * $stripeFee - 0.3, 2),
                    '$' .
                        number_format(
                            $payment->amount -
                                $payment->afterDiscountBeforeFees * $goPrepFee -
                                ($payment->afterDiscountBeforeFees *
                                    $stripeFee -
                                    0.3),
                            2
                        )
                ];
            });

        if ($type !== 'pdf') {
            $payments->prepend([
                'Payment Date',
                'Subtotal',
                'Sales Tax',
                'PreFee Total',
                'GoPrep Fee',
                'Stripe Fee',
                'Total'
            ]);
        }

        return $payments->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.payments_pdf';
    }
}
