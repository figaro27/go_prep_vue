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

        if($type === 'invoice.payment_succeeded') {
          $subId = $obj->get('subscription', null);
          $amountPaid = $obj->get('amount_paid', null);

          // Meal plan paused
          if(!$amountPaid) {
            return 'Amount paid = 0. Meal plan paused. Skipping renewal';
          }

          $subscription = null;
          if($subId) {
            $subId = substr($subId, 4);
            $subscription = Subscription::where('stripe_id', $subId)->first();
          }

          if($subscription) {
            // Make sure status is set to 'active'
            //if($subscription->isPaused()) {
            //  $subscription->resume(false);
            //}

            // Process renewal
            $subscription->renew($obj, $event);
            return 'Meal plan renewed';
          }
          else {
            return 'Meal plan not found';
          }
        }
        elseif($type === 'invoice.payment_failed') {
          $subId = $obj->get('subscription', null);
          $subscription = null;

          if($subId) {
            $subId = substr($subId, 4);
            $subscription = Subscription::where('stripe_id', $subId)->first();
          }

          if(!$subscription) {
            return 'Meal plan not found';
          }

          $subscription->paymentFailed($obj, $event);

          // Set status to 'paused'
          //$subscription->pause(false);
          //return 'Meal plan paused';
        }
        elseif($type === 'customer.subscription.deleted') {
          
        }
    }
}