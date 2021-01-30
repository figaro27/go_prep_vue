<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Store;
use App\Refund;
use App\OrderTransaction;
use App\Order;
use App\UserDetail;
use Illuminate\Support\Carbon;

class getRefundsFromStripe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:getRefundsFromStripe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populates the new Refunds table';

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
        $count = 0;
        foreach ($stores as $store) {
            try {
                $acct = $store->settings->stripe_account;
                \Stripe\Stripe::setApiKey($acct['access_token']);

                // Looping every 3 months since launch since Stripe limits you to getting only 100 at a time.

                $timestamp = Carbon::parse('2019-01-01')->timestamp;
                $stop = false;

                while (!$stop) {
                    $refunds = \Stripe\Refund::all([
                        'limit' => 100,
                        'created' => [
                            'lte' => $timestamp
                        ]
                    ]);
                    foreach ($refunds as $refund) {
                        try {
                            $individualRefund = \Stripe\Refund::retrieve(
                                $refund['id']
                            );
                            $created = Carbon::createFromTimestamp(
                                $individualRefund['created']
                            );

                            $orderTransaction = OrderTransaction::where(
                                'stripe_id',
                                $refund['charge']
                            )->first();
                            if ($orderTransaction) {
                                $order_number = Order::where(
                                    'id',
                                    $orderTransaction->order_id
                                )
                                    ->pluck('order_number')
                                    ->first();
                                $newRefund = new Refund();
                                $newRefund->created_at = $created;
                                $newRefund->store_id = $store['id'];
                                $newRefund->stripe_id = $refund['id'];
                                $newRefund->charge_id = $refund['charge'];
                                $newRefund->user_id =
                                    $orderTransaction->user_id;
                                $newRefund->order_id =
                                    $orderTransaction->order_id;
                                $newRefund->order_number = $order_number;
                                $newRefund->card_id =
                                    $orderTransaction->card_id;
                                $newRefund->amount = $refund['amount'] / 100;
                                $newRefund->save();
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
