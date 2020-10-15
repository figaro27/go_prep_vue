<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use Illuminate\Support\Carbon;
use App\ReportRecord;

class Subscriptions
{
    use Exportable;

    protected $store;

    public function __construct(Store $store, $params = [])
    {
        $this->store = $store;
    }

    public function exportData($type = null)
    {
        $params = $this->params;
        $subscriptions = $this->store
            ->subscriptions()
            ->where('status', '!=', 'cancelled')
            ->with(['user', 'orders', 'orders.meals'])
            ->get()
            ->map(function ($sub) {
                return [
                    $sub->status,
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
                'Status',
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

        $reportRecord = ReportRecord::where(
            'store_id',
            $this->store->id
        )->first();
        $reportRecord->subscriptions += 1;
        $reportRecord->update();

        return $subscriptions->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.subscriptions_pdf';
    }
}
