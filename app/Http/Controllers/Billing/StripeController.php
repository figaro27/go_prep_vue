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

                if ($orderTransaction && $orderTransaction->type === 'order') {
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
