<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use App\ReportRecord;
use Illuminate\Support\Carbon;

class Customers
{
    use Exportable;

    protected $store;

    public function __construct(Store $store, $params = [])
    {
        $this->store = $store;
        $this->params = $params;
        $this->params->put('store', $this->store->details->name);
        $this->params->put('report', 'Customers');
        $this->params->put('date', Carbon::now()->format('m-d-Y'));
    }

    public function exportData($type = null)
    {
        $customers = $this->store->customers->map(function ($customer) {
            return [
                $customer['name'],
                $customer['email'],
                $customer['phone'],
                $customer['address'],
                $customer['city'],
                $customer['zip'],
                $customer['created_at'],
                $customer['total_payments'],
                '$' . $customer['total_paid'],
                $customer['last_order']
            ];
        });

        if ($type !== 'pdf') {
            $customers->prepend([
                'Name',
                'Email',
                'Phone',
                'Address',
                'City',
                'Zip',
                'Customer Since',
                'Total Orders',
                'Total Paid',
                'Last Order'
            ]);
        }

        $reportRecord = ReportRecord::where(
            'store_id',
            $this->store->id
        )->first();
        $reportRecord->customers += 1;
        $reportRecord->update();

        return $customers->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.customers_pdf';
    }
}
