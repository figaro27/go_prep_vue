<?php

namespace App\Http\Controllers\Billing;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Subscription;
use App\Http\Controllers\Controller;

class StripeController extends Controller
{
    public function event(Request $request)
    {
        $event = collect($request->json());
        $data = collect($event->get('data', []));
        $obj = collect($data->get('object', []));

        $type = $event->get('type', null);

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
        }
    }
}
