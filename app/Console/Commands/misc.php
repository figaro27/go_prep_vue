<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Store;
use App\Customer;
use App\User;
use App\UserDetail;
use Illuminate\Support\Carbon;
use App\PurchasedGiftCard;
use App\StoreSetting;
use App\MealMealTag;
use App\Meal;
use App\MealSize;
use App\Order;
use App\MealAttachment;
use App\MealMealPackageComponentOption;
use App\MealPackageComponentOption;
use App\MealPackageComponent;
use App\MealMealPackageAddon;
use App\MealPackageAddon;
use App\MealPackage;
use App\Subscription;
use App\StorePlan;
use App\StorePlanTransaction;

class misc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:misc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Miscellaneous scripts. Intentionally left blank. Code changed / added directly on server when needed.';

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
        $key = 'sk_live_lyBbZ71GozcirrBo4LFEReX8';
        \Stripe\Stripe::setApiKey($key);

        $storePlans = StorePlan::where(
            'stripe_subscription_id',
            '!=',
            null
        )->get();

        foreach ($storePlans as $storePlan) {
            $this->info($storePlan->store_id);

            $invoices = \Stripe\Invoice::all([
                'subscription' => $storePlan->stripe_subscription_id
            ]);
            foreach ($invoices as $invoice) {
                if ($invoice['charge']) {
                    $charge = \Stripe\Charge::retrieve($invoice['charge']);
                }

                $periodEnd = null;

                if (isset($charge)) {
                    if ($storePlan->period == 'monthly') {
                        $periodEnd = Carbon::createFromTimestamp(
                            $charge['created']
                        )
                            ->addMonthsNoOverflow(1)
                            ->toDateTimeString();
                    } else {
                        $periodEnd = Carbon::createFromTimestamp(
                            $charge['created']
                        )
                            ->addYears(1)
                            ->toDateTimeString();
                    }
                }

                $storePlanTransaction = new StorePlanTransaction();
                $storePlanTransaction->store_id = $storePlan->store_id;
                $storePlanTransaction->store_plan_id = $storePlan->id;
                $storePlanTransaction->stripe_id = $invoice['id'];
                $storePlanTransaction->amount = isset($charge)
                    ? $charge['amount']
                    : $invoice['amount_paid'];
                $storePlanTransaction->currency = $invoice['currency'];
                $storePlanTransaction->period_start = isset($charge)
                    ? Carbon::createFromTimestamp(
                        $charge['created']
                    )->toDateTimeString()
                    : null;
                $storePlanTransaction->period_end = $periodEnd;
                $storePlanTransaction->card_brand = isset($charge)
                    ? $charge['payment_method_details']['card']['brand']
                    : null;
                $storePlanTransaction->card_expiration = isset($charge)
                    ? $charge['payment_method_details']['card']['exp_month'] .
                        '/' .
                        $charge['payment_method_details']['card']['exp_year']
                    : null;
                $storePlanTransaction->card_last4 = isset($charge)
                    ? $charge['payment_method_details']['card']['last4']
                    : null;
                $storePlanTransaction->receipt_url = (isset($charge) &&
                    isset($charge['receipt_url']) &&
                    $charge['receipt_url'] !== null
                        ? $charge['receipt_url']
                        : isset($invoice['hosted_invoice_url']) &&
                            $invoice['hosted_invoice_url'] !== null)
                    ? $invoice['hosted_invoice_url']
                    : null;
                $storePlanTransaction->created = Carbon::createFromTimestamp(
                    $invoice['created']
                )->toDateTimeString();
                $storePlanTransaction->save();
            }
        }
    }
}
