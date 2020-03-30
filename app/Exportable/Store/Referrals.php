<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use App\Referral;

class Referrals
{
    use Exportable;

    protected $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function exportData($type = null)
    {
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
                    '.com/?r=' .
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
        return $referrals;
    }

    public function exportPdfView()
    {
        return 'reports.referrals_pdf';
    }
}
