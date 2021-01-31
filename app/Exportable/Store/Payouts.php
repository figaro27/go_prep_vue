<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use Carbon\Carbon;
use App\ReportRecord;
use Akaunting\Money\Money;
use Payout;

class Payouts
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
        $params = $this->params;

        $weekAgo = Carbon::now()
            ->subDays('7')
            ->startOfDay()
            ->toDateTimeString();

        $startDate = isset($params['startDate'])
            ? Carbon::parse($params['startDate'])->timestamp
            : $weekAgo;
        $endDate = isset($params['endDate'])
            ? Carbon::parse($params['endDate'])->timestamp
            : Carbon::now();

        $currency = $this->store->settings->currency;

        // $acct = $this->store->settings->stripe_account;
        // \Stripe\Stripe::setApiKey($acct['access_token']);

        // $payouts = \Stripe\Payout::all([
        //     'created[gte]' => $startDate,
        //     'created[lte]' => $endDate
        // ]);

        // $payouts = collect($payouts->data)->map(function ($payout) use (
        //     $currency
        // ) {
        //     return [
        //         'Initiated' => Carbon::createFromTimestamp(
        //             $payout->created
        //         )->format('D, m/d/y'),
        //         'Arrival Date' => Carbon::createFromTimestamp(
        //             $payout->arrival_date
        //         )->format('D, m/d/y'),
        //         'Total' => Money::$currency($payout->amount)->format(),
        //         'Status' => $payout->status
        //     ];
        // });

        // $includeTransactions = $params['includeTransactions'];

        // if ($includeTransactions){
        //     $payouts = $payouts->toArray();
        //     foreach ($payouts as $i => $payout){
        //         $transactions = collect(\Stripe\BalanceTransaction::all([
        //             'payout' => $payout['ID']
        //         ])->data)->map(function ($transaction) {
        //             return [
        //                 $transaction->created,
        //                 $transaction->amount
        //             ];
        //         });
        //         array_push($payouts[$i], $transactions);

        //     }
        // }

        $payouts = $this->store->payouts
            ->where('created', '>=', $startDate)
            ->where('created', '<=', $endDate)
            ->map(function ($payout) use ($currency) {
                return [
                    'Initiated' => $payout->created,
                    'Arrival Date' => $payout->arrival_date,
                    'Bank Name' => $payout->bank_name,
                    'Total' => Money::$currency($payout->amount)->format(),
                    'Status' => $payout->status
                ];
            });

        if ($type !== 'pdf') {
            $payouts->prepend([
                'Initiated',
                'Arrival Date',
                'Bank Name',
                'Total',
                'Status'
            ]);
        }

        $reportRecord = ReportRecord::where(
            'store_id',
            $this->store->id
        )->first();
        $reportRecord->payouts += 1;
        $reportRecord->update();

        return $payouts;
    }

    public function exportPdfView()
    {
        return 'reports.payouts_pdf';
    }
}
