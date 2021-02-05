<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use App\Referral;
use App\ReportRecord;
use Illuminate\Support\Carbon;

class Referrals
{
    use Exportable;

    protected $store;

    public function __construct(Store $store, $params = [])
    {
        $this->params = $params;
        $this->store = $store;
    }

    public function exportData($type = null)
    {
        $this->params->put('store', $this->store->details->name);
        $this->params->put('report', 'Referrals');
        $this->params->put('date', Carbon::now()->format('m-d-Y'));

        $referrals = Referral::where('store_id', $this->store->id)
            ->with('user')
            ->get()
            ->map(function ($referral) {
                $referralUrlCode = User::where('id', $referral['user_id'])
                    ->pluck('referralUrlCode')
                    ->first();
                $host = $this->store->details->host
                    ? $this->store->details->host
                    : "goprep";
                $referralUrl =
                    'http://' .
                    $this->store->details->domain .
                    '.' .
                    $host .
                    '.com/customer/menu?r=' .
                    $referralUrlCode;
                return [
                    $referral['user']['name'],
                    $referral['user']['email'],
                    $referral['referredCustomers'],
                    $referral['ordersReferred'],
                    $referral['amountReferred'],
                    $referralUrl,
                    $referral['code'],
                    $referral['balance']
                ];
            });

        if ($type !== 'pdf') {
            $referrals->prepend([
                'Name',
                'Email',
                'Referred Customers',
                'Referred Orders',
                'Total Revenue',
                'Referral Url',
                'Redeem Code',
                'Balance'
            ]);
        }

        $reportRecord = ReportRecord::where(
            'store_id',
            $this->store->id
        )->first();
        $reportRecord->referrals += 1;
        $reportRecord->update();

        return $referrals;
    }

    public function exportPdfView()
    {
        return 'reports.referrals_pdf';
    }
}
