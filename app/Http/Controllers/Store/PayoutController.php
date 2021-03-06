<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use App\Order;
use App\OrderTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\Paginator;
use App\Payout;

class PayoutController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index(Request $request, $page = -1, $pageSize = -1)
    // {
    //     $start = $request->query('start', Carbon::today());
    //     $end = $request->query('end', null);

    //     $acct = $this->store->settings->stripe_account;
    //     \Stripe\Stripe::setApiKey($acct['access_token']);
    //     $payouts = \Stripe\Payout::all([
    //         'limit' => 20,
    //         'starting_after' => 'po_1HoIeGHoLjZBBJivBhqDr8zi'
    //     ]);

    //     $payouts->bank = $this->getBank();
    //     // $payouts->data[] = $payouts->data[0];
    //     // $payouts->data[] = $payouts->data[1];
    //     // $payouts->data[] = $payouts->data[2];
    //     // $payouts->data[] = $payouts->data[3];
    //     // $payouts->data[] = $payouts->data[4];

    //     $payouts = collect($payouts->data);

    //     return $payouts;

    //     if ($page === -1) {
    //         return $payouts;
    //     }

    //     // Paginate
    //     Paginator::currentPageResolver(function () use ($page) {
    //         return $page;
    //     });

    //     return $payouts->paginate($pageSize);
    // }

    // public function getPayoutsWithDates(Request $request)
    // {
    //     $acct = $this->store->settings->stripe_account;
    //     \Stripe\Stripe::setApiKey($acct['access_token']);

    //     $startDate = isset($request['start_date'])
    //         ? Carbon::parse($request['start_date'])->timestamp
    //         : null;
    //     $endDate = isset($request['end_date'])
    //         ? Carbon::parse($request['end_date'])->timestamp
    //         : null;

    //     if (!$endDate) {
    //         $endDate = $startDate;
    //     }

    //     $payouts = \Stripe\Payout::all([
    //         'created[gte]' => $startDate,
    //         'created[lte]' => $endDate
    //     ]);

    //     $payouts->bank = $this->getBank();

    //     return $payouts;
    // }

    public function index()
    {
        $weekAgo = Carbon::now()
            ->subDays('7')
            ->startOfDay()
            ->toDateTimeString();

        $payouts = Payout::where('store_id', $this->store->id)
            ->where('created', '>=', $weekAgo)
            ->get();

        return $payouts;
    }

    public function getPayoutsWithDates(Request $request)
    {
        $startDate = isset($request['start_date'])
            ? Carbon::parse($request['start_date'])
            : null;
        $endDate = isset($request['end_date'])
            ? Carbon::parse($request['end_date'])
            : null;

        if (!$endDate) {
            $endDate = $startDate;
        }

        $payouts = Payout::where('store_id', $this->store->id)
            ->where('created', '>=', $startDate)
            ->where('created', '<=', $endDate)
            ->get();

        return $payouts;
    }

    public function getBank()
    {
        return \Stripe\Account::allExternalAccounts(
            $this->store->settings->stripe_id,
            [
                'object' => 'bank_account'
            ]
        )->data[0]->bank_name;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    public function getBalanceHistory(Request $request)
    {
        // Get the transactions that make up the payout from Stripe
        $payout = $request->get('payout');
        $payoutId = $payout['stripe_id'];

        $application_fee = $this->store->settings->application_fee;

        $acct = $this->store->settings->stripe_account;
        \Stripe\Stripe::setApiKey($acct['access_token']);
        $balanceTransactions = \Stripe\BalanceTransaction::all([
            'payout' => $payoutId,
            'limit' => 100
        ])->data;

        // Removing the first item which Stripe returns as the payout itself.
        array_shift($balanceTransactions);

        // Get all order transactions
        $orderTransactions = OrderTransaction::where(
            'store_id',
            $this->store->id
        )->get();

        $payoutOrders = [];
        $payoutCharges = [];
        $payoutTransfers = [];
        // Getting the associated orders & charges from viewing the Stripe charge ID

        foreach ($balanceTransactions as $balanceTransaction) {
            $charge = $balanceTransaction->source;

            $orderTransaction = $orderTransactions
                ->filter(function ($transaction) use ($charge) {
                    return $transaction->stripe_id === $charge;
                })
                ->first();

            if ($orderTransaction && $orderTransaction->type === 'order') {
                $orderTransaction->order->preTransactionFeeAmount =
                    $orderTransaction->order->amount;
                $orderTransaction->order->transactionFee =
                    ($orderTransaction->order->afterDiscountBeforeFees +
                        $orderTransaction->order->chargedAmount) *
                    ($application_fee / 100);
                if (
                    !$orderTransaction->order->cashOrder &&
                    $this->store->settings->payment_gateway === 'stripe' &&
                    $orderTransaction->order->amount > 0.5
                ) {
                    $orderTransaction->order->transactionFee +=
                        $orderTransaction->order->originalAmount * 0.029 + 0.3;
                }
                $orderTransaction->order->originalAmount -=
                    $orderTransaction->order->transactionFee;
                $payoutOrders[] = $orderTransaction->order;
            }
            if ($orderTransaction && $orderTransaction->type === 'charge') {
                $orderTransaction->preTransactionFeeAmount =
                    $orderTransaction->amount;
                $orderTransaction->transactionFee =
                    $orderTransaction->amount * ($application_fee / 100);
                $orderTransaction->transactionFee +=
                    $orderTransaction->amount * 0.029 + 0.3;
                $orderTransaction->order->amount =
                    $orderTransaction->amount -
                    $orderTransaction->transactionFee;

                $orderTransaction->order->created_at =
                    $orderTransaction->created_at;
                $payoutCharges[] = $orderTransaction->order;
            }
            if ($orderTransaction && $orderTransaction->type === 'refund') {
                $orderTransaction->order->amount =
                    $orderTransaction->amount * -1;
                $orderTransaction->order->created_at =
                    $orderTransaction->created_at;
                $payoutCharges[] = $orderTransaction->order;
            }
            if ($balanceTransaction->type === 'transfer') {
                $payoutTransfers[] = $balanceTransaction;
            }
        }

        // Mapping both orders & charges then merging them together.

        $payoutOrders = collect($payoutOrders)
            ->map(function ($payment) {
                return [
                    'created_at' => $payment->paid_at,
                    'order_number' => $payment->order_number,
                    'customer' => $payment->customer_name,
                    'amount' => $payment->originalAmount,
                    'type' => $payment->manual
                        ? 'Manual Order'
                        : 'Customer Order'
                ];
            })
            ->toArray();

        $payoutCharges = collect($payoutCharges)
            ->map(function ($charge) {
                return [
                    'created_at' => Carbon::parse(
                        $charge->created_at
                    )->toDateTimeString(),
                    'order_number' => $charge->order_number,
                    'customer' => $charge->customer_name,
                    'amount' => $charge->amount,
                    'type' => $charge->amount >= 0 ? 'Charge' : "Refund"
                ];
            })
            ->toArray();

        $payoutTransfers = collect($payoutTransfers)
            ->map(function ($transfer) {
                return [
                    'created_at' => Carbon::createFromTimestamp(
                        $transfer['created']
                    ),
                    'order_number' => '',
                    'customer' => '',
                    'amount' => $transfer['amount'] / 100,
                    'type' => "Transfer"
                ];
            })
            ->toArray();

        $payoutTransactions = array_merge($payoutOrders, $payoutCharges);

        $payoutTransactions = array_merge(
            $payoutTransactions,
            $payoutTransfers
        );

        return $payoutTransactions;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
