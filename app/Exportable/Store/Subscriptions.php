<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use Illuminate\Support\Carbon;

class Subscriptions
{
    use Exportable;

    protected $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function exportData()
    {
        $subscriptions = $this->store->subscriptions()->with(['user', 'orders', 'orders.meals'])
            ->get()
            ->map(function ($sub) {
                return [
                    $sub->notes,
                    $sub->stripe_id,
                    $sub->user->name,
                    $sub->user->details->address,
                    $sub->user->details->zip,
                    $sub->user->details->phone,
                    $sub->amount,
                    $sub->created_at,
                    date('l', mktime(0, 0, 0, 0, $sub->delivery_day)),
                ];
            });

        return $subscriptions->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.subscriptions_pdf';
    }
}
