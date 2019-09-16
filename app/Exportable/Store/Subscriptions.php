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

    public function exportData($type = null)
    {
        $subscriptions = $this->store
            ->subscriptions()
            ->with(['user', 'orders', 'orders.meals'])
            ->get()
            ->map(function ($sub) {
                return [
                    $sub->stripe_id,
                    $sub->user->name,
                    $sub->user->details->address,
                    str_pad($sub->user->details->zip, 5, 0, STR_PAD_LEFT),
                    $sub->user->details->phone,
                    '$' . $sub->amount,
                    $sub->created_at->format('m/d/Y'),
                    date('l', mktime(0, 0, 0, 0, $sub->delivery_day))
                ];
            });

        if ($type !== 'pdf') {
            $subscriptions->prepend([
                'Subscription #',
                'Name',
                'Address',
                'Zip',
                'Phone',
                'Total Price',
                'Subscription Created',
                'Delivery Day'
            ]);
        }
        return $subscriptions->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.subscriptions_pdf';
    }
}
