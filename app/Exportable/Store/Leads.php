<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use App\UserDetail;
use App\ReportRecord;
use Illuminate\Support\Carbon;

class Leads
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
        $this->params->put('report', 'Leads');
        $this->params->put('date', Carbon::now()->format('m-d-Y'));

        $leads = UserDetail::where('store_id', $this->store->id)
            ->where('total_payments', 0)
            ->where('multiple_store_orders', 0)
            ->get();

        $multipleStoreOrderUsers = UserDetail::where(
            'store_id',
            $this->store->id
        )
            ->where('total_payments', '>=', 1)
            ->where('multiple_store_orders', 1)
            ->get();

        // The user could have created orders but on a different store
        foreach ($multipleStoreOrderUsers as $userDetail) {
            $addToList = true;
            foreach ($userDetail->user->orders as $order) {
                if ($order->store_id === $this->store->id) {
                    $addToList = false;
                }
            }
            if ($addToList) {
                $leads->push($userDetail);
            }
        }

        $leads = $leads->map(function ($lead) {
            return [
                $lead['firstname'] . ' ' . $lead['lastname'],
                $lead['email'],
                $lead['phone'],
                $lead['city'],
                $lead['address'],
                $lead['zip'],
                $lead['created_at']->format('m/d/Y')
            ];
        });

        if ($type !== 'pdf') {
            $leads->prepend([
                'Name',
                'Email',
                'Phone',
                'Address',
                'City',
                'Zip',
                'Menu Viewed'
            ]);
        }

        $reportRecord = ReportRecord::where(
            'store_id',
            $this->store->id
        )->first();
        $reportRecord->leads += 1;
        $reportRecord->update();

        return $leads;
    }

    public function exportPdfView()
    {
        return 'reports.leads_pdf';
    }
}
