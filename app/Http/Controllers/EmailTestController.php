<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Store\CancelledSubscription;
use App\Mail\Store\ReadyToPrint;
use App\Mail\Customer\DeliveryToday;
use App\Mail\Customer\MealPLan;
use App\Mail\Customer\SubscriptionRenewing;
use App\Customer;
use App\Card;
use App\StoreDetail;
use App\Order;
use App\Subscription;
use App\StoreSetting;

class EmailTestController extends Controller
{
    public function storeCancelledSubscription(){
    	$customer = Customer::first();
    	$email = new CancelledSubscription([
                'customer' => $customer,
            ]);
    	Mail::to('store@goprep.com')->send($email);
    }

    public function storeReadyToPrint(){
    	$storeDetails = StoreDetail::first();
    	$email = new ReadyToPrint([
                'storeDetail' => $storeDetails
            ]);
    	Mail::to('store@goprep.com')->send($email);
    }

    public function customerDeliveryToday(){
    	$customer = Customer::first();
    	$order = Order::orderBy('created_at', 'desc')->first();
    	$card = Card::first();
    	$settings = StoreSetting::first();
    	$email = new DeliveryToday([
                'customer' => $customer,
                'order' => $order,
                'settings' => $order->store->settings
            ]);
    	Mail::to('customer@goprep.com')->send($email);
    }

    public function customerMealPlan(){
    	$subscription = Subscription::first();
    	$order = Order::orderBy('created_at', 'desc')->first();
    	$email = new MealPLan([
                'subscription' => $subscription,
                'order' => $order
            ]);
    	Mail::to('customer@goprep.com')->send($email);
    }

    public function customerSubscriptionRenewing(){
    	$customer = Customer::first();
    	$subscription = Subscription::first();
    	$email = new SubscriptionRenewing([
    			'customer' => $customer,
                'subscription' => $subscription,
            ]);
    	Mail::to('customer@goprep.com')->send($email);
    }

    
}
