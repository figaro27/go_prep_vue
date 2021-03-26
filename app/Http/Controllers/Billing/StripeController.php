<?php

namespace App\Http\Controllers\Billing;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Subscription;
use App\Http\Controllers\Controller;
use App\Payout;
use App\StoreSetting;
use App\Store;
use Illuminate\Support\Carbon;
use App\Order;
use App\OrderTransaction;
use App\StorePlan;
use App\StorePlanTransaction;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    public function event(Request $request)
    {
        $event = collect($request->json());
        $data = collect($event->get('data', []));
        $obj = collect($data->get('object', []));

        $type = $event->get('type', null);

        $storeId = StoreSetting::where('stripe_id', $event->get('account'))
            ->pluck('store_id')
            ->first();

        $storeSetting = StoreSetting::where(
            'stripe_id',
            $event->get('account')
        )->first();

        // Store Plan Subscription Renewals
        if ($type === 'charge.succeeded') {
            $stripeCustomerId = (string) $obj['customer'];
            $storePlan = StorePlan::where(
                'stripe_customer_id',
                $stripeCustomerId
            )->first();
            if ($storePlan) {
                $storePlan->charge_failed = null;
                $storePlan->charge_failed_reason = null;
                $storePlan->charge_attempts = 0;
                $storePlan->last_charged = Carbon::now();
                $storePlan->update();

                $card = $obj['payment_method_details']['card'];

                $storePlanTransaction = new StorePlanTransaction();
                $storePlanTransaction->store_plan_id = $storePlan->id;
                $storePlanTransaction->store_id = $storePlan->store_id;
                $storePlanTransaction->stripe_id = $obj['id'];
                $storePlanTransaction->amount = $obj['amount'];
                $storePlanTransaction->description = $obj['description'];
                $storePlanTransaction->currency = $obj['currency'];
                $storePlanTransaction->card_brand = $card['brand'];
                $storePlanTransaction->card_expiration =
                    $card['exp_month'] . '/' . $card['exp_year'];
                $storePlanTransaction->card_last4 = $card['last4'];
                $storePlanTransaction->period_start = Carbon::createFromTimestamp(
                    $obj['created']
                )->toDateTimeString();
                $storePlanTransaction->period_end =
                    ($storePlan && $storePlan->period == 'monthly') ||
                    !$storePlan
                        ? Carbon::createFromTimestamp($obj['created'])
                            ->addMonthsNoOverflow(1)
                            ->toDateTimeString()
                        : Carbon::createFromTimestamp($obj['created'])
                            ->addYears(1)
                            ->toDateTimeString();
                $storePlanTransaction->receipt_url = $obj['receipt_url'];
                $storePlanTransaction->save();
            }
        }
        if ($type === 'charge.failed') {
            $storePlan = StorePlan::where(
                'stripe_customer_id',
                $obj['customer']
            )->first();
            if ($storePlan) {
                $storePlan->charge_failed = Carbon::createFromTimestamp(
                    $obj['created']
                )->toDateTimeString();
                $storePlan->charge_failed_reason =
                    $obj['failure_message'] ?? 'Charge Failed';
                $storePlan->charge_attempts += 1;
                $storePlan->update();
            }
        }

        //$subscriptions = Subscription::all();

        // Processing the renewal for voided invoices (from paused subscriptions). This will create a new order but keep it marked as unpaid in order to continue the weekly flow of new orders.
        if (
            $type === 'invoice.payment_succeeded' ||
            $type === 'invoice.voided'
        ) {
            // Subscriptions are internal now
            return;

            $subId = $obj->get('subscription', null);

            $subscription = null;
            if ($subId) {
                $subId = substr($subId, 4);
                $subscription = Subscription::where(
                    'stripe_id',
                    $subId
                )->first();
            }

            if ($subscription) {
                // Process renewal
                $subscription->renew($obj, $event);
                return 'Subscription renewed';
            } else {
                return 'Subscription not found';
            }
        } elseif ($type === 'invoice.payment_failed') {
            $subId = $obj->get('subscription', null);
            $subscription = null;

            if ($subId) {
                $subId = substr($subId, 4);
                $subscription = Subscription::where(
                    'stripe_id',
                    $subId
                )->first();
            }

            if (!$subscription) {
                return 'Subscription not found';
            }

            $subscription->paymentFailed($obj, $event);

            // Set status to 'paused'
            //$subscription->pause(false);
            //return 'Subscription paused';
        } elseif ($type === 'customer.subscription.deleted') {
            $subId = $obj->get('subscription', null);
            $subscription = null;

            if ($subId) {
                $subId = substr($subId, 4);
                $subscription = Subscription::where(
                    'stripe_id',
                    $subId
                )->first();
            }

            if (!$subscription) {
                return 'Subscription not found';
            }

            $subscription->cancel();
        } elseif ($type === 'payout.paid') {
            $payout = Payout::where('stripe_id', $obj['id'])->first();
            if ($payout) {
                $payout->status = 'Paid';
                $payout->update();
            }
        } elseif ($type === 'payout.created') {
            if ($event->get('account') !== null) {
                $bank_name = \Stripe\Account::allExternalAccounts(
                    $event->get('account'),
                    [
                        'object' => 'bank_account'
                    ]
                );

                $bank_name = $bank_name->data
                    ? $bank_name->data[0]->bank_name
                    : null;

                $payout = new Payout();
                $payout->store_id = $storeId;
                $payout->status = $obj['status'];
                $payout->stripe_id = $obj['id'];
                $payout->bank_id = $obj['destination'];
                $payout->bank_name = $bank_name;
                $payout->created = Carbon::createFromTimestamp(
                    $obj['created']
                )->toDateTimeString();
                $payout->arrival_date = Carbon::createFromTimestamp(
                    $obj['arrival_date']
                )->toDateTimeString();
                $payout->amount = $obj['amount'] / 100;
                $payout->save();

                // Set the payout_id and payout_date to all orders belonging to the payout
                $acct = $storeSetting->stripe_account;
                \Stripe\Stripe::setApiKey($acct['access_token']);

                $balanceTransactions = \Stripe\BalanceTransaction::all([
                    'payout' => $obj['id'],
                    'limit' => 100
                ])->data;

                // Removing the first item which Stripe returns as the payout itself.
                array_shift($balanceTransactions);

                // Get all order transactions
                $orderTransactions = OrderTransaction::where(
                    'store_id',
                    $storeId
                )->get();

                foreach ($balanceTransactions as $balanceTransaction) {
                    $charge = $balanceTransaction->source;

                    $orderTransaction = $orderTransactions
                        ->filter(function ($transaction) use ($charge) {
                            return $transaction->stripe_id === $charge;
                        })
                        ->first();

                    if (
                        $orderTransaction &&
                        $orderTransaction->type === 'order'
                    ) {
                        $orderTransaction->order->payout_date = Carbon::createFromTimestamp(
                            $obj['arrival_date']
                        )->toDateTimeString();
                        $orderTransaction->order->payout_total =
                            $obj['amount'] / 100;
                        $orderTransaction->order->update();
                    }
                }
            }
        }
    }
}
