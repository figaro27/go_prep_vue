<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\FilterPayments;

class PaymentController extends StoreController
{
    public function getPayments(Request $request)
    {
        $filterPayments = new FilterPayments();

        return $this->formatResponse($filterPayments->getPayments($request));
    }

    protected function formatResponse($orders)
    {
        return $orders->map(function ($order) {
            return $order->only([
                'id',
                'paid_at',
                'delivery_date',
                'totalPayments',
                'order_number',
                'customer_name',
                'totalPayments',
                'subtotal',
                'couponReduction',
                'couponCode',
                'mealPlan',
                'salesTax',
                'processingFee',
                'deliveryFee',
                'purchasedGift',
                'gratuity',
                'coolerDeposit',
                'referralReduction',
                'promotionReduction',
                'pointsReduction',
                'chargedAmount',
                'preTransaction',
                'transactionFee',
                'amount',
                'refundedAmount',
                'balance',
                'preTransactionFeeAmount',
                'voided',
                'mealPlanDiscount'
            ]);
        });
    }
}
