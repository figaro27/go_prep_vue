<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Payout;
use App\Store;
use Illuminate\Support\Carbon;

class getPayoutsFromStripe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:getPayoutsFromStripe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populates the new Payouts table';

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

                // Stripe doesn't let you get more than 100...
                $payouts = \Stripe\Payout::all(['limit' => 100]);
                foreach ($payouts as $payout) {
                    try {
                        $newPayout = new Payout();
                        $newPayout->store_id = $store->id;
                        $newPayout->status = ucfirst($payout['status']);
                        $newPayout->stripe_id = $payout['id'];
                        $newPayout->bank_id = $payout['destination'];
                        $newPayout->bank_name = $bank_name;
                        $newPayout->created = Carbon::createFromTimestamp(
                            $payout['created']
                        )->toDateTimeString();
                        $newPayout->arrival_date = Carbon::createFromTimestamp(
                            $payout['arrival_date']
                        )->toDateTimeString();
                        $newPayout->amount = $payout['amount'] / 100;
                        $newPayout->save();
                    } catch (\Exception $e) {
                    }
                }
            } catch (\Exception $e) {
            }
        }
    }
}
