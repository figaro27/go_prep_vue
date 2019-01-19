<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;

class Customers
{
    use Exportable;

    protected $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function exportData()
    {
        return $this->store->customers->map(function($customer) {
          return [
            $customer['name'],
            $customer['phone'],
            $customer['address'],
            $customer['city'],
            $customer['state'],
            $customer['joined'],
            $customer['total_payments'],
            $customer['total_paid'],
            $customer['last_order'],
          ];
        })->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.customers_pdf';
    }
}
