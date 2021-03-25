<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\StorePlan;
use App\StorePlanTransaction;
use Illuminate\Support\Carbon;
use App\User;
use App\Store;

class addStorePlanData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:addStorePlanData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populates StorePlan and StorePlanTransactions after new Billing page development';

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

        $storePlans = StorePlan::all();
        $existingStorePlanStoreIds = [];

        foreach ($storePlans as $storePlan) {
            $existingStorePlanStoreIds[] = $storePlan->store_id;
        }

        $stores = Store::all();

        foreach ($stores as $store) {
            try {
                $user = User::where('id', $store->user_id)->first();
                if (in_array($store->id, $existingStorePlanStoreIds)) {
                    // Create new store plan from existing store plan record
                    $existingStorePlan = StorePlan::where(
                        'store_id',
                        $store->id
                    )->first();

                    if ($existingStorePlan->stripe_subscription_id) {
                        $stripeSubscription = \Stripe\Subscription::retrieve(
                            $existingStorePlan->stripe_subscription_id
                        );
                    }

                    if (isset($stripeSubscription)) {
                        $customer = \Stripe\Customer::retrieve(
                            $stripeSubscription['customer']
                        );
                        $contactEmail = $customer['email'];

                        $planName =
                            isset($stripeSubscription['plan']) &&
                            isset($stripeSubscription['plan']['nickname'])
                                ? $stripeSubscription['plan']['nickname']
                                : 'Pending';
                        $planName = $planName == null ? 'Pending' : $planName;
                        $planName = str_replace('Monthly - ', '', $planName);
                        $planName = str_replace('Annual - ', '', $planName);
                        $planName = strtolower($planName);

                        $cardData = \Stripe\PaymentMethod::all([
                            'customer' => $stripeSubscription['customer'],
                            'type' => 'card'
                        ]);

                        if (
                            $cardData &&
                            isset($cardData['data']) &&
                            isset($cardData['data'][0])
                        ) {
                            $card = $cardData['data'][0]['id'];
                        } else {
                            $card = null;
                        }

                        if ($stripeSubscription['status'] === 'canceled') {
                            $cancelledAt = Carbon::createFromTimestamp(
                                $stripeSubscription['canceled_at']
                            )->toDateTimeString();
                        } else {
                            $cancelledAt = null;
                        }

                        $latestInvoice = \Stripe\Invoice::retrieve(
                            $stripeSubscription['latest_invoice']
                        );
                        if ($latestInvoice) {
                            $lastCharged = Carbon::createFromTimeStamp(
                                $latestInvoice['status_transitions']['paid_at']
                            );
                        }
                    } else {
                        $contactEmail = $user->email;
                        $planName = 'Pending';
                        $card = null;
                        $cancelledAt = null;
                    }

                    $plans = config('plans');
                    if (isset($plans[$planName])) {
                        $plan = collect($plans[$planName]['monthly']);
                    } else {
                        $plan = null;
                    }

                    $storePlan = new StorePlan();
                    $storePlan->store_id = $store->id;
                    $storePlan->store_name = $store->details->name;
                    $storePlan->status = isset($stripeSubscription)
                        ? $stripeSubscription['status']
                        : 'pending';
                    $storePlan->contact_email = $contactEmail;
                    $storePlan->contact_phone = $user->details->phone;
                    $storePlan->contact_name =
                        $user->details->firstname .
                        ' ' .
                        $user->details->lastname;
                    $storePlan->plan_name = $planName;
                    $storePlan->allowed_orders = $plan
                        ? $plan->get('orders')
                        : 0;
                    $storePlan->amount = $existingStorePlan->amount;
                    $storePlan->period = $existingStorePlan->period;
                    $storePlan->day = $existingStorePlan->day;
                    $storePlan->month = isset($stripeSubscription)
                        ? Carbon::createFromTimestamp(
                            $stripeSubscription['start_date']
                        )->month
                        : 0;
                    $storePlan->currency = $existingStorePlan->currency;
                    $storePlan->created_at = $existingStorePlan->created_at;
                    $storePlan->updated_at = $existingStorePlan->updated_at;
                    $storePlan->method = $existingStorePlan->method;
                    $storePlan->stripe_customer_id =
                        $existingStorePlan->stripe_customer_id;
                    $storePlan->stripe_subscription_id =
                        $existingStorePlan->stripe_subscription_id;
                    $storePlan->stripe_card_id = $card;
                    $storePlan->last_charged =
                        isset($lastCharged) &&
                        $lastCharged !== '1970-01-01 00:00:00'
                            ? $lastCharged
                            : null;
                    $storePlan->cancelled_at = $cancelledAt
                        ? $cancelledAt
                        : null;

                    $existingStorePlan->delete();

                    $storePlan->save();

                    if ($storePlan->stripe_subscription_id) {
                        try {
                            $invoices = \Stripe\Invoice::all([
                                'subscription' =>
                                    $storePlan->stripe_subscription_id
                            ]);
                            foreach ($invoices as $invoice) {
                                if ($invoice['charge']) {
                                    $charge = \Stripe\Charge::retrieve(
                                        $invoice['charge']
                                    );
                                }

                                $periodEnd = null;

                                if (isset($charge)) {
                                    if (
                                        $existingStorePlan->period == 'monthly'
                                    ) {
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
                                $storePlanTransaction->store_id = $store->id;
                                $storePlanTransaction->store_plan_id =
                                    $storePlan->id;
                                $storePlanTransaction->stripe_id =
                                    $invoice['id'];
                                $storePlanTransaction->amount = isset($charge)
                                    ? $charge['amount']
                                    : $invoice['amount_paid'];
                                $storePlanTransaction->currency =
                                    $invoice['currency'];
                                $storePlanTransaction->period_start = isset(
                                    $charge
                                )
                                    ? Carbon::createFromTimestamp(
                                        $charge['created']
                                    )->toDateTimeString()
                                    : null;
                                $storePlanTransaction->period_end = $periodEnd;
                                $storePlanTransaction->card_brand = isset(
                                    $charge
                                )
                                    ? $charge['payment_method_details']['card'][
                                        'brand'
                                    ]
                                    : null;
                                $storePlanTransaction->card_expiration = isset(
                                    $charge
                                )
                                    ? $charge['payment_method_details']['card'][
                                            'exp_month'
                                        ] .
                                        '/' .
                                        $charge['payment_method_details'][
                                            'card'
                                        ]['exp_year']
                                    : null;
                                $storePlanTransaction->card_last4 = isset(
                                    $charge
                                )
                                    ? $charge['payment_method_details']['card'][
                                        'last4'
                                    ]
                                    : null;
                                $storePlanTransaction->receipt_url = (isset(
                                        $charge
                                    ) &&
                                    isset($charge['receipt_url']) &&
                                    $charge['receipt_url'] !== null
                                        ? $charge['receipt_url']
                                        : isset(
                                                $invoice['hosted_invoice_url']
                                            ) &&
                                            $invoice['hosted_invoice_url'] !==
                                                null)
                                    ? $invoice['hosted_invoice_url']
                                    : null;
                                $storePlanTransaction->save();
                            }
                        } catch (\Exception $e) {
                            $this->info('transaction error - ' . $e);
                        }
                    }
                    $this->info('A - ' . $store->id);
                } else {
                    $storePlan = new StorePlan();
                    $storePlan->store_id = $store->id;
                    $storePlan->store_name = $store->details->name;
                    $storePlan->status = 'n/a';
                    $storePlan->contact_email = $user ? $user->email : null;
                    $storePlan->contact_phone = $user
                        ? $user->details->phone
                        : null;
                    $storePlan->contact_name = $user
                        ? $user->details->firstname .
                            ' ' .
                            $user->details->lastname
                        : null;
                    $storePlan->plan_name = 'pay-as-you-go';
                    $storePlan->allowed_orders = 0;
                    $storePlan->amount = 0;
                    $storePlan->period = 'n/a';
                    $storePlan->day = 0;
                    $storePlan->month = 0;
                    $storePlan->currency = $store->settings->currency;
                    $storePlan->created_at = $store->created_at;
                    $storePlan->updated_at = $store->created_at;
                    $storePlan->method = 'connect';
                    $storePlan->stripe_customer_id = null;
                    $storePlan->stripe_subscription_id = null;
                    $storePlan->stripe_card_id = null;
                    $storePlan->last_charged = null;
                    $storePlan->cancelled_at = null;
                    $storePlan->save();

                    $this->info('B - ' . $store->id);
                }
            } catch (\Exception $e) {
                $this->info('plan error - ' . $e);
            }
        }
    }
}
