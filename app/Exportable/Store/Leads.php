<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
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

        $leads = User::doesntHave('orders')
            ->where('last_viewed_store_id', $this->store->id)
            ->with('details')
            ->get()
            ->map(function ($lead) {
                return [
                    $lead['details']['full_name'],
                    $lead['email'],
                    $lead['details']['phone'],
                    $lead['details']['city'],
                    $lead['details']['address'],
                    $lead['details']['zip'],
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
