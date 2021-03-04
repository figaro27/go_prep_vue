<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Payout;
use App\Store;
use App\Order;
use App\OrderTransaction;
use Illuminate\Support\Carbon;

class updateOrdersWithPayoutsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:updateOrdersWithPayoutsData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fills in the two new columns in orders for payout_id and payout_date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stores = Store::with('settings')->get();

        foreach ($stores as $store) {
            try {
                $acct = $store->settings->stripe_account;
                \Stripe\Stripe::setApiKey($acct['access_token']);
                $bank_name = \Stripe\Account::allExternalAccounts(
                    $store->settings->stripe_id,
                    ['object' => 'bank_account']
                )->data[0]->bank_name;

                // Looping every 3 months since launch since Stripe limits you to getting only 100 at a time.

                $timestamp = Carbon::parse('2019-01-01')->timestamp;
                $stop = false;

                while (!$stop) {
                    $payouts = \Stripe\Payout::all([
                        'limit' => 100,
                        'arrival_date' => [
                            'lte' => $timestamp
                        ]
                    ]);
                    foreach ($payouts as $payout) {
                        try {
                            // Set the payout_id and payout_date to all orders belonging to the payout
                            $balanceTransactions = \Stripe\BalanceTransaction::all(
                                [
                                    'payout' => $payout['id'],
                                    'limit' => 100
                                ]
                            )->data;

                            // Removing the first item which Stripe returns as the payout itself.
                            array_shift($balanceTransactions);

                            // Get all order transactions
                            $orderTransactions = OrderTransaction::where(
                                'store_id',
                                $store->id
                            )->get();

                            foreach (
                                $balanceTransactions
                                as $balanceTransaction
                            ) {
                                $charge = $balanceTransaction->source;

                                $orderTransaction = $orderTransactions
                                    ->filter(function ($transaction) use (
                                        $charge
                                    ) {
                                        return $transaction->stripe_id ===
                                            $charge;
                                    })
                                    ->first();

                                if (
                                    $orderTransaction &&
                                    $orderTransaction->type === 'order'
                                ) {
                                    $orderTransaction->order->payout_id = Payout::where(
                                        'stripe_id',
                                        $payout['id']
                                    )
                                        ->pluck('id')
                                        ->first();
                                    $orderTransaction->order->payout_date = Carbon::createFromTimestamp(
                                        $payout['arrival_date']
                                    )->toDateTimeString();
                                    $orderTransaction->order->update();
                                }
                            }
                        } catch (\Exception $e) {
                        }
                    }

                    $timestamp = Carbon::createFromTimestamp($timestamp);

                    if (!$timestamp->isPast()) {
                        $stop = true;
                    }
                    $timestamp->addDays(90);
                    $timestamp = Carbon::parse($timestamp)->timestamp;
                }
            } catch (\Exception $e) {
            }
        }
    }
}
