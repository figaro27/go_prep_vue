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
        return $filterPayments->getPayments($request);
    }
}
